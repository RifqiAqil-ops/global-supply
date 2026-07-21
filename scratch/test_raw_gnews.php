<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$key = config('gscrip.api.gnews.key');
echo "Testing GNews Key: {$key}\n";

$response = \Illuminate\Support\Facades\Http::get('https://gnews.io/api/v4/top-headlines', [
    'category' => 'general',
    'lang' => 'en',
    'apikey' => $key
]);

echo "Status: " . $response->status() . "\n";
echo "Body: " . substr($response->body(), 0, 500) . "\n";
