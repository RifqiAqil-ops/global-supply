<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    /**
     * Display a listing of the published articles.
     */
    public function index(Request $request)
    {
        $query = Article::with('author')
            ->where('status', 'published')
            ->orderByDesc('published_at');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('summary', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $articles = $query->paginate(6)->withQueryString();

        return view('user.articles.index', compact('articles'));
    }

    /**
     * Display the specified article.
     */
    public function show(string $slug)
    {
        $article = Article::with('author')
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        return view('user.articles.show', compact('article'));
    }
}
