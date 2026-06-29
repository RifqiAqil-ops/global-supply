<div class="card card-premium mb-4 {{ $class ?? '' }}">
    @if(isset($title) || isset($headerActions))
        <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between py-3" style="border-color: var(--color-border) !important;">
            @if(isset($title))
                <h5 class="card-title text-white mb-0 fs-6 fw-semibold">
                    @if(isset($icon))
                        <i class="{{ $icon }} me-2 text-primary"></i>
                    @endif
                    {{ $title }}
                </h5>
            @endif
            @if(isset($headerActions))
                <div class="card-header-actions">
                    {{ $headerActions }}
                </div>
            @endif
        </div>
    @endif
    
    <div class="card-body py-3 px-4">
        {{ $slot }}
    </div>

    @if(isset($footer))
        <div class="card-footer bg-transparent border-top py-3" style="border-color: var(--color-border) !important;">
            {{ $footer }}
        </div>
    @endif
</div>
