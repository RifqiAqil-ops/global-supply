@props([
    'lines' => 3,
    'circle' => null // diameter e.g. '40px' or null
])

<div class="skeleton-wrapper w-100 py-2">
    @if($circle)
        <div class="skeleton-circle mb-3" style="width: {{ $circle }}; height: {{ $circle }};"></div>
    @endif
    
    @for($i = 0; $i < $lines; $i++)
        @php
            $width = match($i % 3) {
                0 => '100%',
                1 => '85%',
                2 => '60%',
            };
        @endphp
        <div class="skeleton-line" style="width: {{ $width }};"></div>
    @endfor
</div>
