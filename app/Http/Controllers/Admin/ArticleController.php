<?php

namespace App\Http\Controllers\Admin;

use App\Events\ArticlePublished;
use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::with('author')->orderByDesc('created_at');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('summary', 'like', "%{$search}%");
        }

        $articles = $query->paginate(15)->withQueryString();
        return view('admin.articles.index', compact('articles'));
    }

    public function create()
    {
        return view('admin.articles.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'content' => ['required', 'string'],
            'status' => ['required', 'in:draft,published'],
        ]);

        $slug = Str::slug($validated['title']);
        $originalSlug = $slug;
        $count = 1;
        while (Article::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        $article = Article::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'summary' => $validated['summary'],
            'content' => $validated['content'],
            'author_id' => auth()->id(),
            'status' => $validated['status'],
            'published_at' => $validated['status'] === 'published' ? now() : null,
        ]);

        try {
            ArticlePublished::dispatch('created', $article->toArray());
        } catch (\Throwable $e) {}

        return redirect()->route('admin.articles.index')->with('success', 'Article created successfully.');
    }

    public function edit(Article $article)
    {
        return view('admin.articles.edit', compact('article'));
    }

    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'content' => ['required', 'string'],
            'status' => ['required', 'in:draft,published'],
        ]);

        $article->title = $validated['title'];
        $article->summary = $validated['summary'];
        $article->content = $validated['content'];
        $article->status = $validated['status'];

        if ($validated['status'] === 'published' && !$article->published_at) {
            $article->published_at = now();
        }

        $article->save();

        try {
            ArticlePublished::dispatch('updated', $article->toArray());
        } catch (\Throwable $e) {}

        return redirect()->route('admin.articles.index')->with('success', 'Article updated successfully.');
    }

    public function destroy(Article $article)
    {
        $articleData = $article->toArray();
        $article->delete();

        try {
            ArticlePublished::dispatch('deleted', $articleData);
        } catch (\Throwable $e) {}

        return redirect()->route('admin.articles.index')->with('success', 'Article deleted successfully.');
    }
}
