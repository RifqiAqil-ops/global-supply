<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\NewsArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NewsController extends Controller
{
    /**
     * Show the Geopolitical News aggregation page.
     */
    public function index(Request $request)
    {
        $hasArticles = NewsArticle::whereNotNull('source_url')
            ->where('source_url', '!=', '')
            ->where('source_url', 'not like', '%example.com%')
            ->exists();

        // Auto-sync live GNews API synchronously if database has zero live articles
        if (!$hasArticles) {
            try {
                $gnewsService = app(\App\Services\External\GNewsService::class);
                if ($gnewsService->hasApiKey()) {
                    $gnewsService->syncAllNews();
                }
            } catch (\Throwable $e) {
                Log::warning("Auto-sync GNews on empty DB failed: " . $e->getMessage());
            }
        }

        // Build fresh query AFTER syncAllNews completes
        $query = NewsArticle::with('country')
            ->whereNotNull('source_url')
            ->where('source_url', '!=', '')
            ->where('source_url', 'not like', '%example.com%');

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('country', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by sentiment
        if ($request->filled('sentiment')) {
            $query->where('sentiment', $request->input('sentiment'));
        }

        $articles = $query->orderByDesc('published_at')->paginate(12);

        // Stats for real GNews articles
        $realQuery = NewsArticle::whereNotNull('source_url')->where('source_url', '!=', '')->where('source_url', 'not like', '%example.com%');
        $totalArticles = (clone $realQuery)->count();
        $negativeCount = (clone $realQuery)->where('sentiment', 'negative')->count();
        $positiveCount = (clone $realQuery)->where('sentiment', 'positive')->count();
        $neutralCount = (clone $realQuery)->where('sentiment', 'neutral')->count();

        return view('user.news', compact(
            'articles', 'totalArticles', 'negativeCount', 'positiveCount', 'neutralCount'
        ));
    }
}
