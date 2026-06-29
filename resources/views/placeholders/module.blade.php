@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="container-fluid py-4">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-decoration-none text-muted">Home</a></li>
            <li class="breadcrumb-item active text-white" aria-current="page">{{ $title }}</li>
        </ol>
    </nav>

    <!-- Placeholder Card Area -->
    <div class="card card-premium border-0 py-5 my-3">
        <div class="card-body text-center py-5">
            <div class="display-3 text-primary mb-4">
                <i class="bi {{ $icon ?? 'bi-cone-striped' }} animate-pulse"></i>
            </div>
            <h2 class="text-white mb-2 fw-semibold">{{ $title }}</h2>
            <p class="text-muted mb-4 mx-auto" style="max-width: 540px;">
                The <strong>{{ strtolower($title) }}</strong> module is currently scheduled for implementation in the upcoming development sprint. All database tables and repositories have been mapped and are ready for functional logic integration.
            </p>
            
            <!-- Warning badge with exact Coming in Next Development Phase string -->
            <div class="d-inline-flex align-items-center gap-2 px-4 py-2.5 rounded-pill bg-warning bg-opacity-10 text-warning border border-warning border-opacity-20 fw-semibold fs-7 shadow-sm">
                <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                Coming in Next Development Phase
            </div>
        </div>
    </div>
</div>

<style>
@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.6; transform: scale(0.95); }
}
.animate-pulse {
    animation: pulse 2.5s infinite ease-in-out;
}
</style>
@endsection
