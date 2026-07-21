<?php

namespace App\Services\External;

use App\DTOs\NewsDTO;
use App\Http\Clients\BaseApiClient;
use App\Models\Country;
use App\Models\NewsArticle;
use App\Repositories\Contracts\NewsRepositoryInterface;
use Carbon\Carbon;
use Database\Seeders\NewsArticleSeeder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class GNewsService extends BaseApiClient
{
    protected NewsRepositoryInterface $newsRepository;

    /**
     * Topic queries mapped to categories.
     * Each query consumes 1 API request from the daily limit.
     */
    protected array $topicQueries = [
        'economic'     => ['trade OR tariff OR economy OR inflation'],
        'geopolitical' => ['geopolitics OR sanctions OR conflict OR defense'],
        'logistics'    => ['shipping OR port OR logistics OR freight'],
    ];

    public function __construct(NewsRepositoryInterface $newsRepository)
    {
        parent::__construct('https://gnews.io/api/v4', 'GNews');
        $this->newsRepository = $newsRepository;
    }

    /**
     * Retrieve GNews API key from configuration or environment.
     */
    public function getApiKey(): ?string
    {
        return '7344b28905b738f61c307796531fda31';
    }

    /**
     * Check if the GNews API key is configured.
     */
    public function hasApiKey(): bool
    {
        return !empty($this->getApiKey());
    }

    /**
     * Synchronize news for all configured topic categories.
     * Uses batched, regional queries to stay within daily rate limits.
     *
     * @return array Summary: ['fetched' => X, 'saved' => Y, 'duplicates' => Z, 'failed' => W]
     */
    public function syncAllNews(): array
    {
        $summary = [
            'fetched'    => 0,
            'saved'      => 0,
            'duplicates' => 0,
            'failed'     => 0,
        ];

        if (!$this->hasApiKey()) {
            Log::warning("GNews API key is not configured. Skipping live news sync.");
            return $summary;
        }

        $apiKey = $this->getApiKey();

        // 1. Fetch top headlines first
        try {
            $params = [
                'query' => [
                    'category' => 'business',
                    'lang'     => 'en',
                    'max'      => 10,
                    'apikey'   => $apiKey,
                    'token'    => $apiKey,
                ]
            ];
            $response = $this->request('GET', 'top-headlines', $params);
            if (!empty($response) && isset($response['articles'])) {
                foreach ($response['articles'] as $article) {
                    $summary['fetched']++;
                    $sourceUrl = $article['url'] ?? null;
                    if (!$sourceUrl || $this->newsRepository->existsByUrl($sourceUrl)) {
                        continue;
                    }
                    try {
                        $parsed = $this->parseArticle($article, 'economic', 'top-headlines');
                        NewsArticle::create($parsed);
                        $summary['saved']++;
                    } catch (Throwable $e) {
                        Log::error("Failed to save top-headline article: " . $e->getMessage());
                    }
                }
            }
        } catch (Throwable $e) {
            Log::warning("GNews top-headlines request failed: " . $e->getMessage());
        }

        // 2. Fetch category topic queries
        foreach ($this->topicQueries as $category => $queries) {
            foreach ($queries as $queryString) {
                sleep(1);
                try {
                    $params = [
                        'query' => [
                            'q'      => $queryString,
                            'lang'   => 'en',
                            'max'    => 10,
                            'apikey' => $apiKey,
                            'token'  => $apiKey,
                            'sortby' => 'publishedAt',
                        ]
                    ];

                    $response = $this->request('GET', 'search', $params);

                    if (empty($response) || !isset($response['articles'])) {
                        Log::warning("GNews returned empty or invalid response for query: '{$queryString}'");
                        $summary['failed']++;
                        continue;
                    }

                    foreach ($response['articles'] as $article) {
                        $summary['fetched']++;

                        $sourceUrl = $article['url'] ?? null;
                        if (!$sourceUrl) {
                            $summary['failed']++;
                            continue;
                        }

                        // Prevent duplicates — skip articles already in database
                        if ($this->newsRepository->existsByUrl($sourceUrl)) {
                            $summary['duplicates']++;
                            continue;
                        }

                        try {
                            $parsed = $this->parseArticle($article, $category, $queryString);
                            NewsArticle::create($parsed);
                            $summary['saved']++;
                        } catch (Throwable $e) {
                            Log::error("Failed to save news article: " . $e->getMessage());
                            $summary['failed']++;
                        }
                    }

                    // Flush general news cache after each batch
                    Cache::forget("news.latest.{$category}");
                    Cache::forget('news.latest.general');

                } catch (Throwable $e) {
                    Log::error("GNews API request failed for query '{$queryString}': " . $e->getMessage());
                    $summary['failed']++;
                    $summary['errors'][] = $e->getMessage();
                }
            }
        }

        return $summary;
    }

    /**
     * Fetch news for a specific country.
     *
     * @param int $countryId
     * @return array Summary
     */
    public function syncCountryNews(int $countryId): array
    {
        $summary = ['fetched' => 0, 'saved' => 0, 'duplicates' => 0, 'failed' => 0];

        if (!$this->hasApiKey()) {
            Log::warning("GNews API key is not configured. Skipping country news sync.");
            return $summary;
        }

        $country = Country::find($countryId);
        if (!$country) {
            return $summary;
        }

        $apiKey = $this->getApiKey();
        $queryString = $country->name . ' economy trade';

        try {
            $params = [
                'query' => [
                    'q'      => $queryString,
                    'lang'   => 'en',
                    'max'    => 5,
                    'apikey' => $apiKey,
                    'token'  => $apiKey,
                    'sortby' => 'publishedAt',
                ]
            ];

            $response = $this->request('GET', 'search', $params);

            if (empty($response) || !isset($response['articles'])) {
                return $summary;
            }

            foreach ($response['articles'] as $article) {
                $summary['fetched']++;
                $sourceUrl = $article['url'] ?? null;
                if (!$sourceUrl || $this->newsRepository->existsByUrl($sourceUrl)) {
                    $summary['duplicates']++;
                    continue;
                }

                $parsed = $this->parseArticle($article, 'economic', $queryString, $countryId);
                NewsArticle::create($parsed);
                $summary['saved']++;
            }

            Cache::forget("news.country.{$countryId}");

        } catch (Throwable $e) {
            Log::error("GNews country sync failed for '{$country->name}': " . $e->getMessage());
            $summary['failed']++;
        }

        return $summary;
    }

    /**
     * Get the latest news articles. Returns existing database records first to guarantee instant response times, syncing in background if needed.
     */
    public function getLatestArticles(int $limit = 20, ?string $category = null, bool $forceRefresh = false): EloquentCollection
    {
        $cacheKey = "news.latest." . ($category ?? 'general') . ".{$limit}";
        $ttl = config('gscrip.cache_ttl.news', 3600); // 1 hour

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        // 1. Read existing articles from database first
        $articles = $this->newsRepository->latestArticles($limit, $category);

        if ($articles->isNotEmpty() && !$forceRefresh) {
            foreach ($articles as $art) {
                $art->isCached = true;
            }
            return $articles;
        }

        // 2. If database is empty or forceRefresh, attempt live API sync
        try {
            if ($this->hasApiKey()) {
                $this->syncAllNews();
                $articles = $this->newsRepository->latestArticles($limit, $category);
                foreach ($articles as $art) {
                    $art->isCached = false;
                }
                if ($articles->isNotEmpty()) {
                    Cache::put($cacheKey, $articles, $ttl);
                    return $articles;
                }
            }
        } catch (Throwable $e) {
            Log::warning("GNews API call failed: " . $e->getMessage());
        }

        foreach ($articles as $art) {
            $art->isCached = true;
        }

        return $articles;
    }

    /**
     * Get news for a country. Returns database records first, attempts live sync if empty.
     */
    public function getCountryNews(int $countryId, int $limit = 10, bool $forceRefresh = false): EloquentCollection
    {
        $cacheKey = "news.country.{$countryId}.{$limit}";
        $ttl = config('gscrip.cache_ttl.news', 3600);

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        // 1. Read existing articles from database first
        $articles = $this->newsRepository->articlesByCountry($countryId, $limit);

        if ($articles->isNotEmpty() && !$forceRefresh) {
            foreach ($articles as $art) {
                $art->isCached = true;
            }
            return $articles;
        }

        // 2. If empty, attempt live API sync
        try {
            if ($this->hasApiKey()) {
                $this->syncCountryNews($countryId);
                $articles = $this->newsRepository->articlesByCountry($countryId, $limit);
                foreach ($articles as $art) {
                    $art->isCached = false;
                }
                if ($articles->isNotEmpty()) {
                    Cache::put($cacheKey, $articles, $ttl);
                    return $articles;
                }
            }
        } catch (Throwable $e) {
            Log::warning("GNews country API call failed for ID '{$countryId}': " . $e->getMessage());
        }

        foreach ($articles as $art) {
            $art->isCached = true;
        }

        return $articles;
    }

    /**
     * Search news articles from the database.
     */
    public function searchArticles(string $query, int $limit = 20): EloquentCollection
    {
        return $this->newsRepository->search($query, $limit);
    }

    /**
     * Analyze keyword-based sentiment.
     */
    private function analyzeSentiment(string $text): string
    {
        $text = strtolower($text);

        try {
            $positiveKeywords = \App\Models\PositiveWord::pluck('word')->toArray();
            $negativeKeywords = \App\Models\NegativeWord::pluck('word')->toArray();
        } catch (\Throwable $e) {
            $positiveKeywords = ['growth', 'increase', 'improved', 'recovery', 'surge', 'boom', 'agreement', 'deal', 'cooperation', 'reform', 'investment', 'expand'];
            $negativeKeywords = ['war', 'conflict', 'crisis', 'recession', 'inflation', 'sanctions', 'tariff', 'disruption', 'decline', 'collapse', 'protest', 'instability', 'ban', 'shortage'];
        }

        if (empty($positiveKeywords)) {
            $positiveKeywords = ['growth', 'increase', 'improved', 'recovery', 'surge', 'boom', 'agreement', 'deal', 'cooperation', 'reform', 'investment', 'expand'];
        }
        if (empty($negativeKeywords)) {
            $negativeKeywords = ['war', 'conflict', 'crisis', 'recession', 'inflation', 'sanctions', 'tariff', 'disruption', 'decline', 'collapse', 'protest', 'instability', 'ban', 'shortage'];
        }

        $positiveCount = 0;
        $negativeCount = 0;

        foreach ($positiveKeywords as $keyword) {
            if (str_contains($text, $keyword)) {
                $positiveCount++;
            }
        }
        foreach ($negativeKeywords as $keyword) {
            if (str_contains($text, $keyword)) {
                $negativeCount++;
            }
        }

        if ($positiveCount > $negativeCount) {
            return 'positive';
        } elseif ($negativeCount > $positiveCount) {
            return 'negative';
        }

        return 'neutral';
    }

    /**
     * Parse a raw GNews article to database fields.
     */
    private function parseArticle(array $article, string $category, string $searchQuery, ?int $countryId = null): array
    {
        $title = $article['title'] ?? '';
        $description = $article['description'] ?? null;
        $combinedText = $title . ' ' . ($description ?? '');

        return [
            'country_id'   => $countryId,
            'title'        => mb_substr($title, 0, 500),
            'description'  => $description,
            'content'      => isset($article['content']) ? mb_substr($article['content'], 0, 5000) : null,
            'source_name'  => $article['source']['name'] ?? null,
            'source_url'   => $article['url'] ?? '',
            'image_url'    => $article['image'] ?? null,
            'category'     => $category,
            'sentiment'    => $this->analyzeSentiment($combinedText),
            'search_query' => $searchQuery,
            'published_at' => isset($article['publishedAt']) ? Carbon::parse($article['publishedAt']) : null,
            'fetched_at'   => now(),
        ];
    }

    /**
     * Flush all news cache.
     */
    public function flushCache(): void
    {
        Cache::flush();
    }
}
