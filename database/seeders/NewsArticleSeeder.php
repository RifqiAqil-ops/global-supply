<?php

namespace Database\Seeders;

use App\Models\NewsArticle;
use App\Services\External\GNewsService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class NewsArticleSeeder extends Seeder
{
    public function run(): void
    {
        $gnewsService = app(GNewsService::class);
        
        // Live GNews API sync only — zero fake/demo seeder fallback
        if ($gnewsService->hasApiKey()) {
            try {
                $gnewsService->syncAllNews();
            } catch (\Throwable $e) {
                Log::warning("NewsArticleSeeder: GNews live API sync attempt failed: " . $e->getMessage());
            }
        } else {
            Log::info("NewsArticleSeeder: GNews API key not configured. Skipping live news sync.");
        }
    }
}
