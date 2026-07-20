@extends('layouts.app')

@section('title', 'Failed Queue Jobs')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h3 text-white fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-octagon text-danger"></i> Failed Queue Jobs Manager
            </h1>
            <p class="text-muted small mb-0">
                Inspect, retry, or flush failed background queue workers directly without requiring SSH access.
            </p>
        </div>
        @if($failedJobs->count() > 0)
        <div class="d-flex gap-2">
            <form action="{{ route('admin.failed-jobs.retry-all') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary px-3 py-2 fw-semibold d-flex align-items-center gap-2">
                    <i class="bi bi-arrow-counterclockwise"></i> Retry All Failed Jobs
                </button>
            </form>
            <form action="{{ route('admin.failed-jobs.flush') }}" method="POST" onsubmit="return confirm('Clear all failed jobs log?')">
                @csrf
                <button type="submit" class="btn btn-outline-danger px-3 py-2 fw-semibold d-flex align-items-center gap-2">
                    <i class="bi bi-trash"></i> Flush All Logs
                </button>
            </form>
        </div>
        @endif
    </div>

    <!-- Table of Failed Jobs -->
    <div class="card card-premium border-0 shadow-sm">
        <div class="card-header bg-transparent border-secondary border-opacity-20 py-3">
            <h5 class="card-title text-white fw-bold mb-0">Failed Worker Tasks Log</h5>
        </div>
        <div class="card-body p-0">
            @if($failedJobs->count() > 0)
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0" style="background: transparent;">
                    <thead class="table-light table-group-divider small text-uppercase">
                        <tr>
                            <th class="ps-4">ID / UUID</th>
                            <th>Queue</th>
                            <th>Failed At</th>
                            <th>Exception Summary</th>
                            <th class="pe-4 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($failedJobs as $job)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-white">#{{ $job->id }}</div>
                                    <div class="font-monospace text-muted small" style="font-size: 0.72rem;">{{ Str::limit($job->uuid, 18) }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-dark border border-secondary text-secondary font-monospace">
                                        {{ $job->queue }}
                                    </span>
                                </td>
                                <td class="text-white small">
                                    {{ \Carbon\Carbon::parse($job->failed_at)->format('Y-m-d H:i:s') }}
                                    <div class="text-muted small">{{ \Carbon\Carbon::parse($job->failed_at)->diffForHumans() }}</div>
                                </td>
                                <td class="small" style="max-width: 350px;">
                                    <div class="text-danger font-monospace text-truncate" title="{{ $job->exception }}">
                                        {{ Str::limit($job->exception, 120) }}
                                    </div>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="btn-group">
                                        <form action="{{ route('admin.failed-jobs.retry', $job->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-primary fw-semibold" title="Retry this job">
                                                <i class="bi bi-arrow-counterclockwise"></i> Retry
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.failed-jobs.destroy', $job->id) }}" method="POST" class="d-inline ms-1" onsubmit="return confirm('Delete this failed job entry?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger fw-semibold" title="Delete job">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $failedJobs->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-check-circle display-4 text-success d-block mb-3"></i>
                <h5 class="text-white fw-bold">No Failed Queue Jobs</h5>
                <p class="text-muted small mb-0">All background workers and scheduled tasks are running smoothly without errors.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
