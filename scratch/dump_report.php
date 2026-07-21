<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\NewsArticle;

$liveArticles = NewsArticle::whereNotNull('source_url')
    ->where('source_url', '!=', '')
    ->orderBy('id', 'desc')
    ->take(10)
    ->get();

$demoArticles = NewsArticle::where(function($q) {
        $q->whereNull('source_url')->orWhere('source_url', '');
    })
    ->orderBy('id', 'desc')
    ->take(5)
    ->get();

echo "=======================================================================================================================\n";
echo "                                  WAYPOINT NEWS ARTICLES AUDIT & VERIFICATION REPORT                                   \n";
echo "=======================================================================================================================\n\n";

printf("%-55s | %-20s | %-12s | %s\n", "TITLE", "SOURCE NAME", "STATUS", "SOURCE URL");
echo str_repeat("-", 125) . "\n";

foreach ($liveArticles as $a) {
    $shortTitle = mb_substr($a->title, 0, 52) . (mb_strlen($a->title) > 52 ? '...' : '');
    $shortSource = mb_substr($a->source_name ?? 'N/A', 0, 18);
    printf("%-55s | %-20s | %-12s | %s\n", $shortTitle, $shortSource, "LIVE API", $a->source_url);
}

foreach ($demoArticles as $a) {
    $shortTitle = mb_substr($a->title, 0, 52) . (mb_strlen($a->title) > 52 ? '...' : '');
    $shortSource = mb_substr($a->source_name ?? 'N/A', 0, 18);
    printf("%-55s | %-20s | %-12s | %s\n", $shortTitle, $shortSource, "DEMO DATA", "(None - Fallback Badge Displayed)");
}

echo "=======================================================================================================================\n";
