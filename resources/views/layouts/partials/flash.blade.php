@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 card-premium shadow-sm mb-4" role="alert" style="border-left: 4px solid var(--color-accent) !important;">
        <div class="d-flex align-items-center gap-2 text-white">
            <i class="bi bi-check-circle-fill text-success fs-5"></i>
            <div>
                <strong>Success:</strong> {{ session('success') }}
            </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 card-premium shadow-sm mb-4" role="alert" style="border-left: 4px solid #dc3545 !important;">
        <div class="d-flex align-items-center gap-2 text-white">
            <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
            <div>
                <strong>Error:</strong> {{ session('error') }}
            </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('status'))
    <div class="alert alert-info alert-dismissible fade show border-0 card-premium shadow-sm mb-4" role="alert" style="border-left: 4px solid var(--color-primary) !important;">
        <div class="d-flex align-items-center gap-2 text-white">
            <i class="bi bi-info-circle-fill text-primary fs-5"></i>
            <div>
                {{ session('status') }}
            </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 card-premium shadow-sm mb-4" role="alert" style="border-left: 4px solid #dc3545 !important;">
        <div class="d-flex align-items-center gap-2 text-white align-items-start">
            <i class="bi bi-exclamation-octagon-fill text-danger fs-5 mt-1"></i>
            <div>
                <strong class="d-block mb-1">Please fix the following validation errors:</strong>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
