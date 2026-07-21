<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$key = config('gscrip.api.gnews.key');

echo "Testing GNews WITH token AND apikey params...\n";
$resp1 = \Illuminate\Support\Facades\Http::withoutVerifying()->get('https://gnews.io/api/v4/top-headlines', [
    'category' => 'business',
    'lang' => 'en',
    'max' => 10,
    'apikey' => $key,
    'token' => $key,
]);
echo "Status: " . $resp1->status() . " | Body: " . substr($resp1->body(), 0, 300) . "\n\n";

echo "Testing GNews WITH ONLY apikey param...\n";
$resp2 = \Illuminate\Support\Facades\Http::withoutVerifying()->get('https://gnews.io/api/v4/top-headlines', [
    'category' => 'business',
    'lang' => 'en',
    'max' => 10,
    'apikey' => $key,
]);
echo "Status: " . $resp2->status() . " | Body: " . substr($resp2->body(), 0, 300) . "\n";
