@props([
    'size' => '', // 'sm' or default
    'type' => 'border', // 'border' or 'grow'
    'color' => 'primary'
])

@php
    $sizeClass = $size === 'sm' ? ($type === 'grow' ? 'spinner-grow-sm' : 'spinner-border-sm') : '';
    $typeClass = $type === 'grow' ? 'spinner-grow' : 'spinner-border';
@endphp

<div class="{{ $typeClass }} {{ $sizeClass }} text-{{ $color }}" role="status">
    <span class="visually-hidden">Loading...</span>
</div>
