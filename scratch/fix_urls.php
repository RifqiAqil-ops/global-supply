<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\NewsArticle;

$count = NewsArticle::where('source_url', 'like', '%gnews.io%')
    ->orWhere('source_url', 'like', '%example.com%')
    ->update(['source_url' => '']);

echo "Updated {$count} placeholder news article URLs to empty string ('').\n";
