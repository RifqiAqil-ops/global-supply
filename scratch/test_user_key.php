<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$userKey = '7344b28905b738f61c307796531fda31';

echo "Testing GNews API with User Key: {$userKey}\n";

$url1 = "https://gnews.io/api/v4/top-headlines?category=general&lang=en&apikey={$userKey}";
$r1 = \Illuminate\Support\Facades\Http::withoutVerifying()->get($url1);
echo "1. top-headlines (apikey param) -> Status: " . $r1->status() . " | Body: " . substr($r1->body(), 0, 300) . "\n\n";

$url2 = "https://gnews.io/api/v4/top-headlines?category=general&lang=en&token={$userKey}";
$r2 = \Illuminate\Support\Facades\Http::withoutVerifying()->get($url2);
echo "2. top-headlines (token param)  -> Status: " . $r2->status() . " | Body: " . substr($r2->body(), 0, 300) . "\n\n";

$url3 = "https://gnews.io/api/v4/search?q=economy&lang=en&apikey={$userKey}";
$r3 = \Illuminate\Support\Facades\Http::withoutVerifying()->get($url3);
echo "3. search (apikey param)       -> Status: " . $r3->status() . " | Body: " . substr($r3->body(), 0, 300) . "\n";
