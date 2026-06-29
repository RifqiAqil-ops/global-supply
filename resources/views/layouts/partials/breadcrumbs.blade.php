@php
    // Determine active route and labels for default breadcrumbs
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
        <ol class="breadcrumb bg-transparent p-0 m-0">
            <li class="breadcrumb-item">
                <a href="{{ url('/') }}" class="text-primary text-decoration-none small">
                    <i class="bi bi-house-door-fill me-1"></i>Home
                </a>
            </li>
            @foreach ($items as $label => $url)
                @if ($loop->last)
                    <li class="breadcrumb-item active text-muted small" aria-current="page">{{ $label }}</li>
                @else
                    <li class="breadcrumb-item">
                        <a href="{{ $url }}" class="text-primary text-decoration-none small">{{ $label }}</a>
                    </li>
                @endif
            @endforeach
        </ol>
    </nav>
@endif
