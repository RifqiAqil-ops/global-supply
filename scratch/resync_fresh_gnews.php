<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\NewsArticle;
use App\Services\External\GNewsService;
use Database\Seeders\NewsArticleSeeder;

echo "1. Wiping old empty/mock articles from database...\n";
NewsArticle::truncate();

echo "2. Running live GNews API synchronization...\n";
$gnewsService = app(GNewsService::class);
$result = $gnewsService->syncAllNews();
echo "Live API Sync Result: Saved " . ($result['saved'] ?? 0) . " live articles, fetched " . ($result['fetched'] ?? 0) . ".\n";

echo "3. Seeding fallback country news...\n";
(new NewsArticleSeeder())->run();

$liveCount = NewsArticle::whereNotNull('source_url')
    ->where('source_url', '!=', '')
    ->where('source_url', 'not like', '%example.com%')
    ->where('source_url', 'not like', '%gnews.io%')
    ->count();

$demoCount = NewsArticle::where(function($q) {
        $q->whereNull('source_url')
          ->orWhere('source_url', '')
          ->orWhere('source_url', 'like', '%example.com%')
          ->orWhere('source_url', 'like', '%gnews.io%');
    })->count();

echo "\n=============================================\n";
echo "DATABASE NEWS ARTICLES SUMMARY:\n";
echo "Total Live API Articles (with valid publisher URLs): {$liveCount}\n";
echo "Total Demo Fallback Articles (with null/empty URLs): {$demoCount}\n";
echo "Total Articles in Database: " . NewsArticle::count() . "\n";
echo "=============================================\n";
