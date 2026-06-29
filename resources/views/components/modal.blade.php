@props([
    'id',
    'title',
    'size' => '' // 'modal-sm', 'modal-lg', 'modal-xl'
])

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog {{ $size }} modal-dialog-centered">
        <div class="modal-content card-premium border-0" style="background-color: var(--color-bg-card);">
            <div class="modal-header border-bottom border-secondary border-opacity-30 py-3 px-4 d-flex align-items-center justify-content-between">
                <h5 class="modal-title text-white fs-5 fw-semibold" id="{{ $id }}Label">{{ $title }}</h5>
                <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4 px-4 text-white">
                {{ $slot }}
            </div>
            @if(isset($footer))
                <div class="modal-footer border-top border-secondary border-opacity-30 py-3 px-4">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
