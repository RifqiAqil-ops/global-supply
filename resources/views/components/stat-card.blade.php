@props([
    'title',
    'value',
    'change' => null,
    'changeType' => 'neutral', // 'up', 'down', 'neutral'
    'icon' => 'bi-circle',
    'iconColor' => 'primary', // bootstrap text colors
    'color' => null,
    'valueId' => null,
])

@php
    // Map theme colors to CSS properties
    $themeColor = $color ?? $iconColor ?? 'primary';
    $uniqId = 'grad_' . bin2hex(random_bytes(4));
    
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
    
    // Determine trend badge border and bg
    $badgeBg = match($changeType) {
        'up' => 'bg-success bg-opacity-10 text-success border-success border-opacity-20',
        'down' => 'bg-danger bg-opacity-10 text-danger border-danger border-opacity-20',
        default => 'bg-secondary bg-opacity-10 text-muted border-secondary border-opacity-20'
    };

    // Hex color map for SVG sparklines
    $hexColor = match($themeColor) {
        'primary' => '#2563EB',
        'success' => '#10B981',
        'warning' => '#F59E0B',
        'danger' => '#EF4444',
        'info' => '#06B6D4',
        default => '#6B7280'
    };

    // Decide sparkline path based on trend
    $sparkPath = match($changeType) {
        'up' => 'M 0 24 Q 20 28, 40 18 T 80 8 T 100 2',
        'down' => 'M 0 4 Q 20 12, 40 8 T 80 20 T 100 28',
        default => 'M 0 15 Q 20 18, 40 12 T 80 16 T 100 15'
    };
    
    $closedPath = $sparkPath . ' L 100 35 L 0 35 Z';
@endphp

<div class="card saas-stat-card border-0 mb-4 h-100 position-relative">
    <!-- Trend Badge (Top Right Corner) -->
    @if($change)
        <div class="position-absolute top-0 end-0 mt-3.5 me-3.5">
            <span class="badge {{ $badgeBg }} border px-2.5 py-1.5" style="font-size: 0.72rem; border-radius: 8px; font-weight: 600;">
                <i class="bi {{ $changeIcon }} me-0.5"></i> {{ $change }}
            </span>
        </div>
    @endif

    <!-- Header Section (Icon Left, Title Right) -->
    <div class="d-flex align-items-center gap-2.5 mb-3" style="padding-right: 70px;">
        <div class="rounded-circle bg-{{ $themeColor }} bg-opacity-10 text-{{ $themeColor }} d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; flex-shrink: 0;">
            <i class="bi {{ $icon }} fs-6"></i>
        </div>
        <span class="stat-title text-muted text-uppercase tracking-wider fw-bold">{{ $title }}</span>
    </div>

    <!-- Value Row -->
    <div class="d-flex align-items-baseline gap-2 mt-auto">
        <h3 class="stat-value display-6 fw-bold mb-0 text-dark" @if($valueId) id="{{ $valueId }}" @endif>{{ $value }}</h3>
    </div>

    <!-- Sparkline Microchart -->
    <div class="mt-3.5" style="height: 30px; opacity: 0.85; margin-left: -1.75rem; margin-right: -1.75rem; margin-bottom: -1.75rem;">
        <svg class="w-100 h-100" viewBox="0 0 100 35" preserveAspectRatio="none">
            <defs>
                <linearGradient id="sparkline-grad-{{ $uniqId }}" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="{{ $hexColor }}" stop-opacity="0.12"></stop>
                    <stop offset="100%" stop-color="{{ $hexColor }}" stop-opacity="0.00"></stop>
                </linearGradient>
            </defs>
            <path d="{{ $closedPath }}" fill="url(#sparkline-grad-{{ $uniqId }})"></path>
            <path d="{{ $sparkPath }}" fill="none" stroke="{{ $hexColor }}" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
        </svg>
    </div>
</div>
