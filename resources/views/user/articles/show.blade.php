@extends('layouts.app')

@section('title', $article->title)

@section('content')
<div class="container-fluid py-4">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('articles.index') }}" class="btn btn-secondary-saas">
            <i class="bi bi-arrow-left"></i> Back to Reports
        </a>
    </div>

    <!-- Article Content -->
    <div class="card border-0 shadow-sm p-5" style="border-radius: 24px; border: 1px solid #E5E7EB !important; background-color: #FFFFFF;">
        <div class="d-flex align-items-center gap-2 mb-3">
            <span class="badge bg-primary bg-opacity-10 text-primary">Intelligence Report</span>
            <span class="text-muted small"><i class="bi bi-calendar3 me-1"></i>Published on {{ $article->published_at->format('F d, Y') }}</span>
        </div>
        
        <h1 class="typo-h1 mb-4" style="font-size: 2.25rem;">{{ $article->title }}</h1>
        
        <div class="d-flex align-items-center gap-3 pb-4 mb-4 border-bottom border-light">
            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                <i class="bi bi-person text-muted fs-4"></i>
            </div>
            <div>
                <div class="fw-semibold text-dark">{{ $article->author->name ?? 'System Admin' }}</div>
                <div class="text-muted small">Global Procurement Risk Analyst</div>
            </div>
        </div>

        @if($article->summary)
            <div class="p-4 bg-light rounded-4 mb-4" style="border-left: 4px solid var(--color-primary);">
                <p class="mb-0 text-dark fw-medium">{{ $article->summary }}</p>
            </div>
        @endif

        <div class="article-body text-dark" style="font-size: 1.05rem; line-height: 1.8;">
            {!! nl2br(e($article->content)) !!}
        </div>
    </div>
</div>
@endsection
