@props([
    'title' => 'No Data Available',
    'description' => 'We couldn\'t find any records matching your request.',
    'icon' => 'bi-folder-x'
])

<div class="empty-state-card card-premium mb-4 text-center d-flex flex-column align-items-center justify-content-center p-5">
    <div class="empty-state-icon mb-3">
        <i class="bi {{ $icon }}"></i>
    </div>
    <h3 class="empty-state-title h5 mb-2">{{ $title }}</h3>
    <p class="empty-state-desc small mx-auto mb-4">{{ $description }}</p>
    <div class="d-flex align-items-center justify-content-center gap-2">
        {{ $slot }}
    </div>
</div>
