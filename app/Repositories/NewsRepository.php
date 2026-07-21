<?php

namespace App\Repositories;

use App\Models\NewsArticle;
use App\Repositories\Contracts\NewsRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class NewsRepository extends BaseRepository implements NewsRepositoryInterface
{
    public function __construct(NewsArticle $model)
    {
        parent::__construct($model);
    }

    /**
     * Get the latest articles, optionally filtered by category.
     */
    public function latestArticles(int $limit = 20, ?string $category = null): Collection
    {
        $query = $this->model
            ->whereNotNull('source_url')
            ->where('source_url', '!=', '')
            ->where('source_url', 'not like', '%example.com%')
            ->orderByDesc('published_at');

        if ($category) {
            $query->where('category', $category);
        }

        return $query->limit($limit)->get();
    }

    /**
     * Get news articles for a specific country.
     */
    public function articlesByCountry(int $countryId, int $limit = 10, ?string $category = null): Collection
    {
        $query = $this->model->where('country_id', $countryId)
            ->orderByDesc('published_at');

        if ($category) {
            $query->where('category', $category);
        }

        return $query->limit($limit)->get();
    }

    /**
     * Get news articles by category.
     */
    public function articlesByCategory(string $category, int $limit = 20): Collection
    {
        return $this->model->where('category', $category)
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Search articles by keyword in title or description.
     */
    public function search(string $query, int $limit = 20): Collection
    {
        return $this->model->where('title', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Check if an article with the given source URL already exists.
     */
    public function existsByUrl(string $sourceUrl): bool
    {
        return $this->model->where('source_url', $sourceUrl)->exists();
    }
}
