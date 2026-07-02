@extends('layouts.app')

@section('title', 'Geopolitical News Intelligence')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 text-white mb-1">Geopolitical News Intelligence</h1>
        <p class="text-muted small mb-0">Aggregated supply chain relevant news articles with sentiment analysis across all monitored regions.</p>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <x-stat-card title="Total Articles" :value="$totalArticles" icon="bi-newspaper" color="primary" />
    </div>
    <div class="col-md-3">
        <x-stat-card title="Negative Sentiment" :value="$negativeCount" icon="bi-emoji-frown" color="danger" />
    </div>
    <div class="col-md-3">
        <x-stat-card title="Positive Sentiment" :value="$positiveCount" icon="bi-emoji-smile" color="success" />
    </div>
    <div class="col-md-3">
        <x-stat-card title="Neutral Sentiment" :value="$neutralCount" icon="bi-emoji-neutral" color="secondary" />
    </div>
</div>

<!-- Filters -->
<div class="card card-premium border-0 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('news.index') }}" class="row g-3">
            <div class="col-md-6">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-dark border-secondary border-opacity-40 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control bg-dark border-secondary border-opacity-40 text-white" placeholder="Search articles by title, description, or country..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="sentiment" class="form-select form-select-sm bg-dark border-secondary border-opacity-40 text-white">
                    <option value="">-- All Sentiments --</option>
                    <option value="negative" {{ request('sentiment') === 'negative' ? 'selected' : '' }}>Negative</option>
                    <option value="positive" {{ request('sentiment') === 'positive' ? 'selected' : '' }}>Positive</option>
                    <option value="neutral" {{ request('sentiment') === 'neutral' ? 'selected' : '' }}>Neutral</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary px-3 fw-semibold"><i class="bi bi-funnel me-1"></i> Filter</button>
                <a href="{{ route('news.index') }}" class="btn btn-sm btn-secondary px-3 fw-semibold"><i class="bi bi-arrow-counterclockwise me-1"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Articles Grid -->
<div class="row g-4">
    @forelse($articles as $article)
    <div class="col-md-6 col-lg-4">
        <div class="card card-premium border-0 h-100" style="transition: transform 0.2s;" onmouseenter="this.style.transform='translateY(-3px)'" onmouseleave="this.style.transform='none'">
            @if($article->image_url)
            <img src="{{ $article->image_url }}" class="card-img-top" style="height: 160px; object-fit: cover; opacity: 0.85;" alt="{{ $article->title }}" onerror="this.style.display='none'">
            @else
            <div class="card-img-top d-flex align-items-center justify-content-center" style="height: 160px; background: linear-gradient(135deg, rgba(59,130,246,0.1), rgba(168,85,247,0.1));">
                <i class="bi bi-newspaper display-4 text-muted"></i>
            </div>
            @endif
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center gap-2 mb-2">
                    @if($article->country)
                    <img src="{{ $article->country->flag_url }}" class="rounded" style="width:16px;height:11px;object-fit:cover;" alt="">
                    <span class="text-muted small">{{ $article->country->name }}</span>
                    @endif
                    @php
                        $sentBadge = 'secondary';
                        if ($article->sentiment === 'negative') $sentBadge = 'danger';
                        elseif ($article->sentiment === 'positive') $sentBadge = 'success';
                    @endphp
                    <x-badge type="{{ $sentBadge }}">{{ ucfirst($article->sentiment ?? 'neutral') }}</x-badge>
                </div>
                <h6 class="text-white fw-bold mb-2" style="line-height: 1.4;">{{ Str::limit($article->title, 80) }}</h6>
                <p class="text-muted small mb-3 flex-grow-1">{{ Str::limit($article->description, 120) }}</p>
                <div class="d-flex align-items-center justify-content-between mt-auto">
                    <span class="text-muted small">
                        <i class="bi bi-clock me-1"></i>{{ $article->published_at ? $article->published_at->diffForHumans() : '—' }}
                    </span>
                    @if($article->source_url)
                    <a href="{{ $article->source_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary py-0 px-2 small">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Source
                    </a>
                    @endif
                </div>
            </div>
            @if($article->source_name)
            <div class="card-footer bg-transparent border-top py-2" style="border-color: var(--color-border) !important;">
                <span class="text-muted small"><i class="bi bi-building me-1"></i>{{ $article->source_name }}</span>
            </div>
            @endif
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card card-premium border-0">
            <div class="card-body text-center py-5">
                <i class="bi bi-newspaper display-4 text-muted d-block mb-3"></i>
                <h5 class="text-white">No News Articles Found</h5>
                <p class="text-muted small mb-0">News articles will appear here once the GNews API feed is configured and synced. Add <code>GNEWS_API_KEY</code> to your <code>.env</code> file.</p>
            </div>
        </div>
    </div>
    @endforelse
</div>

@if($articles->hasPages())
<div class="mt-4 border-top border-secondary border-opacity-10 pt-3">
    {{ $articles->links() }}
</div>
@endif
@endsection
