<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$key = '7344b28905b738f61c307796531fda31';

echo "1. Request WITH custom User-Agent 'Waypoint-Client/1.0.0':\n";
$r1 = \Illuminate\Support\Facades\Http::withoutVerifying()
    ->withHeaders(['User-Agent' => 'Waypoint-Client/1.0.0 (Global Supply Chain Intelligence)'])
    ->get("https://gnews.io/api/v4/top-headlines?category=general&lang=en&apikey={$key}");
echo "Status: " . $r1->status() . " | Body: " . substr($r1->body(), 0, 300) . "\n\n";

echo "2. Request WITH standard Browser User-Agent:\n";
$r2 = \Illuminate\Support\Facades\Http::withoutVerifying()
    ->withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'])
    ->get("https://gnews.io/api/v4/top-headlines?category=general&lang=en&apikey={$key}");
echo "Status: " . $r2->status() . " | Body: " . substr($r2->body(), 0, 300) . "\n";
