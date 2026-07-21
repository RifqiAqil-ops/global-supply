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
        <x-stat-card title="Total Articles" :value="$totalArticles" icon="bi-newspaper" color="primary" valueId="stat-news-total" />
    </div>
    <div class="col-md-3">
        <x-stat-card title="Negative Sentiment" :value="$negativeCount" icon="bi-emoji-frown" color="danger" valueId="stat-news-negative" />
    </div>
    <div class="col-md-3">
        <x-stat-card title="Positive Sentiment" :value="$positiveCount" icon="bi-emoji-smile" color="success" valueId="stat-news-positive" />
    </div>
    <div class="col-md-3">
        <x-stat-card title="Neutral Sentiment" :value="$neutralCount" icon="bi-emoji-neutral" color="secondary" valueId="stat-news-neutral" />
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
<div class="row g-4" id="news-articles-grid">
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
                    @if($article->source_url && !str_contains($article->source_url, 'example.com'))
                    <a href="{{ $article->source_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary py-0 px-2 small">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Source
                    </a>
                    @else
                    <span class="badge bg-secondary opacity-75 font-monospace text-uppercase" style="font-size: 0.68rem;" title="Demo Data Fallback Article">
                        <i class="bi bi-info-circle me-1"></i>Demo Data
                    </span>
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
        @if(empty(config('gscrip.api.gnews.key')))
        <!-- News Empty State: Key Missing -->
        <div class="col-12">
            <div class="card card-premium border-0 p-5 text-center" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.05) 0%, rgba(245, 158, 11, 0.01) 100%);">
                <div class="card-body">
                    <div class="display-4 text-warning mb-3">
                        <i class="bi bi-key-fill"></i>
                    </div>
                    <h4 class="text-white fw-bold mb-2">GNews API Key Configuration Required</h4>
                    <p class="text-muted small mx-auto mb-4" style="max-width: 480px;">
                        The geopolitical and news intelligence feed relies on the GNews API to fetch live global logistics and macro articles. To configure this module, please register for a free API key and set it in your local environment.
                    </p>
                    <div class="p-3 rounded bg-dark border border-secondary border-opacity-20 mx-auto mb-4 text-start font-monospace small" style="max-width: 420px; font-size: 0.78rem;">
                        <span class="text-muted"># Add this configuration parameter in your .env file:</span><br>
                        <span class="text-warning">GNEWS_API_KEY</span>=<span class="text-success">"your_api_token_here"</span>
                    </div>
                    <a href="https://gnews.io/docs" target="_blank" rel="noopener noreferrer" class="btn btn-warning btn-sm px-4 py-2 fw-semibold">
                        <i class="bi bi-box-arrow-up-right me-1"></i> View GNews Documentation
                    </a>
                </div>
            </div>
        </div>
        @else
        <!-- News Empty State: Key Present but No Data -->
        <div class="col-12">
            <div class="card card-premium border-0">
                <div class="card-body text-center py-5">
                    <i class="bi bi-newspaper display-4 text-muted d-block mb-3"></i>
                    <h5 class="text-white">No News Articles Aggregated Yet</h5>
                    <p class="text-muted small mb-0">The GNews API Key is configured correctly! Please run the command line sync task to retrieve news updates: <code>php artisan gscrip:sync-news</code></p>
                </div>
            </div>
        </div>
        @endif
    @endforelse
</div>

@if($articles->hasPages())
<div class="mt-4 border-top border-secondary border-opacity-10 pt-3">
    {{ $articles->links() }}
</div>
@endif
@endsection
