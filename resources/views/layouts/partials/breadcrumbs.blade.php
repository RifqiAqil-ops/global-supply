@php
    $route = Route::currentRouteName();
    $defaultBreadcrumbs = [];
    
    if ($route === 'user.dashboard') {
        $defaultBreadcrumbs = ['Dashboard' => '#'];
    } elseif ($route === 'admin.dashboard') {
        $defaultBreadcrumbs = ['Admin Console' => '#'];
    }
    
    $items = isset($breadcrumbs) && is_array($breadcrumbs) ? $breadcrumbs : $defaultBreadcrumbs;
@endphp

@if (count($items) > 0)
    <nav aria-label="breadcrumb" class="mb-4 d-none d-sm-block">
        <ol class="breadcrumb bg-transparent p-0 m-0 align-items-center gap-2">
            <li class="breadcrumb-item d-flex align-items-center">
                <a href="{{ url('/') }}" class="text-muted text-decoration-none small d-flex align-items-center gap-1 hover-primary">
                    <i class="bi bi-house-door-fill" style="font-size: 0.85rem;"></i> Home
                </a>
            </li>
            @foreach ($items as $label => $url)
                <li class="text-muted small" style="font-size: 0.75rem;">/</li>
                @if ($loop->last)
                    <li class="breadcrumb-item active small fw-semibold text-primary" aria-current="page">{{ $label }}</li>
                @else
                    <li class="breadcrumb-item">
                        <a href="{{ $url }}" class="text-muted text-decoration-none small hover-primary">{{ $label }}</a>
                    </li>
                @endif
            @endforeach
        </ol>
    </nav>
@endif
