<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$key = config('gscrip.api.gnews.key');

echo "Testing GNews WITH sortby=publishedAt...\n";
$resp1 = \Illuminate\Support\Facades\Http::get('https://gnews.io/api/v4/search', [
    'q' => 'trade',
    'lang' => 'en',
    'apikey' => $key,
    'sortby' => 'publishedAt'
]);
echo "With sortby -> Status: " . $resp1->status() . " | Body: " . substr($resp1->body(), 0, 300) . "\n\n";

echo "Testing GNews WITHOUT sortby...\n";
$resp2 = \Illuminate\Support\Facades\Http::get('https://gnews.io/api/v4/search', [
    'q' => 'trade',
    'lang' => 'en',
    'apikey' => $key
]);
echo "Without sortby -> Status: " . $resp2->status() . " | Body: " . substr($resp2->body(), 0, 300) . "\n";
