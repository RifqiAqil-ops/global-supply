<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\External\GNewsService;
use App\Models\NewsArticle;

$service = app(GNewsService::class);
$apiKey = config('gscrip.api.gnews.key');

echo "1. Checking GNews API Key Configuration...\n";
echo "GNews API Key Configured: " . ($service->hasApiKey() ? "YES (" . substr($apiKey, 0, 5) . "...)" : "NO") . "\n\n";

echo "2. Making HTTP Request to GNews API endpoint...\n";

try {
    $response = \Illuminate\Support\Facades\Http::timeout(15)
        ->get('https://gnews.io/api/v4/top-headlines', [
            'category' => 'business',
            'lang' => 'en',
            'country' => 'us',
            'max' => 10,
            'apikey' => $apiKey,
        ]);

    echo "HTTP Status Code: " . $response->status() . "\n";
    
    $json = $response->json();
    $totalArticles = isset($json['articles']) ? count($json['articles']) : 0;
    
    echo "Total Articles Returned from GNews API: {$totalArticles}\n";
    echo "Raw Response Snippet:\n";
    echo substr(json_encode($json, JSON_PRETTY_PRINT), 0, 800) . "\n\n";

    echo "3. Testing DB Insertion of returned articles...\n";
    $insertedCount = 0;
    if (!empty($json['articles'])) {
        foreach ($json['articles'] as $art) {
            try {
                $saved = NewsArticle::updateOrCreate(
                    ['source_url' => $art['url']],
                    [
                        'title' => $art['title'] ?? 'Untitled',
                        'description' => $art['description'] ?? '',
                        'content' => $art['content'] ?? '',
                        'source_name' => $art['source']['name'] ?? 'GNews',
                        'source_url' => $art['url'],
                        'image_url' => $art['image'] ?? null,
                        'category' => 'economic',
                        'sentiment' => 'neutral',
                        'published_at' => isset($art['publishedAt']) ? \Carbon\Carbon::parse($art['publishedAt']) : now(),
                        'fetched_at' => now(),
                    ]
                );
                $insertedCount++;
                echo " Successfully inserted: [ID #{$saved->id}] " . mb_substr($saved->title, 0, 50) . "...\n";
            } catch (\Throwable $e) {
                echo " Exception during insertion: " . $e->getMessage() . "\n";
            }
        }
    }
    echo "\nTotal Articles Successfully Inserted/Updated in DB: {$insertedCount}\n";

} catch (\Throwable $e) {
    echo "HTTP Request Exception: " . $e->getMessage() . "\n";
}
