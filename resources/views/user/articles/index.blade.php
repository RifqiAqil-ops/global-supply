@extends('layouts.app')

@section('title', 'Analysis Articles')

@section('content')
<div class="container-fluid py-4">
    <!-- Hero Section -->
    <div class="card border-0 shadow-sm p-4 mb-4" style="background: linear-gradient(135deg, #EFF6FF 0%, #FFFFFF 100%); border-radius: 24px; border: 1px solid #E5E7EB !important;">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <h1 class="typo-h1 mb-1">Supply Chain Analysis Reports</h1>
                <p class="text-muted mb-0">Browse latest analysis updates, geopolitical briefs, and supply route disruptions.</p>
            </div>
            <div>
                <form action="{{ route('articles.index') }}" method="GET" class="d-flex gap-2">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0" style="border-radius: 16px 0 0 16px;"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Search reports..." value="{{ request('search') }}" style="border-radius: 0 16px 16px 0; width: 220px;">
                    </div>
                    <button type="submit" class="btn btn-primary">Filter</button>
                    @if(request()->filled('search'))
                        <a href="{{ route('articles.index') }}" class="btn btn-secondary-saas">Clear</a>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <!-- Articles Grid -->
    <div class="row">
        @forelse($articles as $article)
            <div class="col-md-6 mb-4">
                <div class="card h-100 border-0 shadow-sm p-4 position-relative" style="border-radius: 24px; border: 1px solid #E5E7EB !important; background-color: #FFFFFF;">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge bg-primary bg-opacity-10 text-primary">Intelligence</span>
                        <span class="text-muted small"><i class="bi bi-calendar3 me-1"></i>{{ $article->published_at->format('M d, Y') }}</span>
                    </div>
                    <h3 class="typo-h3 mb-2" style="font-size: 1.25rem;">
                        <a href="{{ route('articles.show', $article->slug) }}" class="text-decoration-none text-dark hover-primary">{{ $article->title }}</a>
                    </h3>
                    <p class="text-muted small mb-4 flex-grow-1">{{ $article->summary ?: Str::limit($article->content, 120) }}</p>
                    <div class="d-flex align-items-center justify-content-between pt-3 border-top border-light">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                <i class="bi bi-person text-muted"></i>
                            </div>
                            <span class="small fw-semibold text-dark">{{ $article->author->name ?? 'System' }}</span>
                        </div>
                        <a href="{{ route('articles.show', $article->slug) }}" class="btn btn-sm btn-ghost p-0 text-primary d-flex align-items-center gap-1">
                            Read Report <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm p-5 text-center" style="border-radius: 24px; border: 1px solid #E5E7EB !important; background-color: #FFFFFF;">
                    <div class="empty-state-card">
                        <div class="empty-state-icon"><i class="bi bi-journal-x"></i></div>
                        <h4 class="empty-state-title">No Analysis Reports Found</h4>
                        <p class="empty-state-desc">We couldn't find any intelligence reports matching your search parameters.</p>
                        <a href="{{ route('articles.index') }}" class="btn btn-primary">Refresh Feed</a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($articles->hasPages())
        <div class="d-flex justify-content-center mt-3">
            {{ $articles->links() }}
        </div>
    @endif
</div>
@endsection
