@props([
    'title' => 'Connection Error',
    'description' => 'Failed to retrieve data from the server. Please check your network and try again.',
    'icon' => 'bi-wifi-off',
    'retryUrl' => null
])

<div class="text-center py-5 px-4 rounded-3 border border-danger border-opacity-10" style="background-color: rgba(220, 53, 69, 0.01);">
    <div class="display-6 text-danger mb-3">
        <i class="bi {{ $icon }}"></i>
    </div>
    <h3 class="h5 text-white mb-2">{{ $title }}</h3>
    <p class="text-muted small mx-auto mb-4" style="max-width: 350px;">{{ $description }}</p>
    @if($retryUrl)
        <a href="{{ $retryUrl }}" class="btn btn-outline-primary btn-sm rounded-pill fw-semibold px-4 py-2">
            <i class="bi bi-arrow-clockwise me-1"></i> Retry Request
        </a>
    @else
        {{ $slot }}
    @endif
</div>
