<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\External\GNewsService;
use App\Models\NewsArticle;

$service = app(GNewsService::class);
echo "Running syncAllNews()...\n";

$res = $service->syncAllNews();
echo "Result:\n" . json_encode($res, JSON_PRETTY_PRINT) . "\n";
echo "Total Articles in DB: " . NewsArticle::count() . "\n";
