<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\NewsArticle;

// Delete all fake / demo fallback articles
$deleted = NewsArticle::where(function($q) {
    $q->whereNull('source_url')
      ->orWhere('source_url', '')
      ->orWhere('source_url', 'like', '%example.com%')
      ->orWhere('source_url', 'like', '%gnews.io%');
})->delete();

echo "Purged {$deleted} demo/fake articles from news_articles table.\n";

$remaining = NewsArticle::count();
echo "Remaining Real GNews API Articles in Database: {$remaining}\n";
