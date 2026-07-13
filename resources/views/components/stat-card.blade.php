@props([
    'title',
    'value',
    'change' => null,
    'changeType' => 'neutral', // 'up', 'down', 'neutral'
    'icon' => 'bi-circle',
    'iconColor' => 'primary', // bootstrap text colors
    'valueId' => null,
])

@php
    $changeColor = match($changeType) {
        'up' => 'text-success',
        'down' => 'text-danger',
        default => 'text-muted'
    };
    $changeIcon = match($changeType) {
        'up' => 'bi-arrow-up-right',
        'down' => 'bi-arrow-down-right',
        default => 'bi-dash'
    };
@endphp

<div class="card card-premium p-4 border-0 mb-4 h-100">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <span class="text-muted small fw-semibold text-uppercase tracking-wider" style="letter-spacing: 0.05em;">{{ $title }}</span>
        <div class="rounded-3 bg-{{ $iconColor }} bg-opacity-10 p-2.5 text-{{ $iconColor }} d-flex align-items-center justify-content-center">
            <i class="bi {{ $icon }} fs-5"></i>
        </div>
    </div>
    <div class="d-flex align-items-baseline gap-2">
        <h3 class="display-6 fw-bold text-white mb-0" @if($valueId) id="{{ $valueId }}" @endif>{{ $value }}</h3>
        @if($change)
            <span class="small {{ $changeColor }} fw-semibold d-flex align-items-center gap-1">
                <i class="bi {{ $changeIcon }}"></i> {{ $change }}
            </span>
        @endif
    </div>
</div>
