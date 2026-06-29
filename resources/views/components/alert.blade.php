@props([
    'type' => 'info', // 'success', 'danger', 'warning', 'info'
    'dismissible' => true
])

@php
    $typeClass = match($type) {
        'success' => 'alert-success border-success text-success bg-success bg-opacity-5',
        'danger' => 'alert-danger border-danger text-danger bg-danger bg-opacity-5',
        'warning' => 'alert-warning border-warning text-warning bg-warning bg-opacity-5',
        default => 'alert-info border-info text-info bg-info bg-opacity-5'
    };
    
    $icon = match($type) {
        'success' => 'bi-check-circle-fill',
        'danger' => 'bi-exclamation-octagon-fill',
        'warning' => 'bi-exclamation-triangle-fill',
        default => 'bi-info-circle-fill'
    };
@endphp

<div class="alert {{ $typeClass }} d-flex align-items-center gap-2 border-0 border-start border-4 rounded-3 p-3 shadow-sm mb-4" role="alert">
    <i class="bi {{ $icon }} fs-5"></i>
    <div class="flex-grow-1 text-white">
        {{ $slot }}
    </div>
    @if($dismissible)
        <button type="button" class="btn-close btn-close-white ms-auto shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
    @endif
</div>
