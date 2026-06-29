@props([
    'title' => 'No Data Available',
    'description' => 'We couldn\'t find any records matching your request.',
    'icon' => 'bi-folder-x'
])

<div class="text-center py-5 px-4 rounded-3 border border-secondary border-opacity-10" style="background-color: rgba(255, 255, 255, 0.01);">
    <div class="display-6 text-muted mb-3">
        <i class="bi {{ $icon }}"></i>
    </div>
    <h3 class="h5 text-white mb-2">{{ $title }}</h3>
    <p class="text-muted small mx-auto mb-4" style="max-width: 320px;">{{ $description }}</p>
    {{ $slot }}
</div>
