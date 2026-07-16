@props([
    'type' => 'secondary' // 'primary', 'success', 'warning', 'danger', 'info', etc.
])

@php
    $bgClass = match($type) {
        'primary' => 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20',
        'success' => 'bg-success bg-opacity-10 text-success border border-success border-opacity-20',
        'warning' => 'bg-warning bg-opacity-10 text-warning border border-warning border-opacity-20',
        'danger' => 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20',
        'info' => 'bg-info bg-opacity-10 text-info border border-info border-opacity-20',
        default => 'bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-20'
    };
@endphp

<span class="badge py-1 px-2.5 rounded-pill fw-semibold text-uppercase tracking-wider {{ $bgClass }}" style="font-size: 0.65rem; letter-spacing: 0.5px;">
    {{ $slot }}
</span>
