@extends('layouts.app')

@section('title', 'Manage Articles')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Area -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="typo-h1 mb-1">Analysis Articles Manager</h1>
            <p class="text-muted small mb-0">Publish and edit logistics insights or geopolitical reports.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <form action="{{ route('admin.articles.index') }}" method="GET" class="d-flex gap-2">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0" style="border-radius: 16px 0 0 16px;"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search articles..." value="{{ request('search') }}" style="border-radius: 0 16px 16px 0; width: 200px;">
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
                @if(request()->filled('search'))
                    <a href="{{ route('admin.articles.index') }}" class="btn btn-secondary-saas">Clear</a>
                @endif
            </form>
            <a href="{{ route('admin.articles.create') }}" class="btn btn-primary">
                <i class="bi bi-journal-plus"></i> Write Article
            </a>
        </div>
    </div>

    <!-- Articles Table Card -->
    <div class="card border-0 shadow-sm p-4" style="border-radius: 24px; border: 1px solid #E5E7EB !important; background-color: #FFFFFF;">
        @if($articles->count() > 0)
            <div class="table-responsive">
                <table class="table table-premium align-middle">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Status</th>
                            <th>Published Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($articles as $article)
                            @php
                                $statusBadge = $article->status === 'published' ? 'success' : 'warning';
                            @endphp
                            <tr>
                                <td class="fw-semibold text-dark">{{ $article->title }}</td>
                                <td class="text-dark">{{ $article->author->name ?? 'System' }}</td>
                                <td>
                                    <span class="badge bg-{{ $statusBadge }} bg-opacity-10 text-{{ $statusBadge }} border border-{{ $statusBadge }} border-opacity-20 px-2 py-1 text-uppercase" style="font-size: 0.68rem;">
                                        {{ $article->status }}
                                    </span>
                                </td>
                                <td class="text-muted small">
                                    {{ $article->published_at ? $article->published_at->format('M d, Y') : 'Not Published' }}
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1.5">
                                        <a href="{{ route('admin.articles.edit', $article->id) }}" class="btn btn-sm btn-outline-primary" title="Edit Article">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.articles.destroy', $article->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this article?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Article">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($articles->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $articles->links() }}
                </div>
            @endif
        @else
            <div class="empty-state-card py-5">
                <div class="empty-state-icon"><i class="bi bi-journal-text"></i></div>
                <h4 class="empty-state-title">No Articles Found</h4>
                <p class="empty-state-desc">There are no analysis articles registered in the system database.</p>
                <a href="{{ route('admin.articles.create') }}" class="btn btn-primary">Write First Article</a>
            </div>
        @endif
    </div>
</div>
@endsection
