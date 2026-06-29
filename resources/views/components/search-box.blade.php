@props([
    'placeholder' => 'Search countries, codes, coordinates...',
    'name' => 'search',
    'value' => ''
])

<div class="position-relative">
    <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-muted">
        <i class="bi bi-search"></i>
    </span>
    <input type="text" name="{{ $name }}" value="{{ $value }}" class="form-control ps-5" placeholder="{{ $placeholder }}">
</div>
