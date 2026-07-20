@extends('layouts.app')

@section('title', 'Operations Center')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h3 text-white fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="bi bi-cpu text-primary"></i> Operations Center
            </h1>
            <p class="text-muted small mb-0">
                Real-time operational monitoring dashboard for application environment, background queue workers, and dataset sync engines.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.health.index') }}" class="btn btn-outline-success px-3 py-2 fw-semibold d-flex align-items-center gap-2">
                <i class="bi bi-heart-pulse-fill"></i> System Health
            </a>
            <a href="{{ route('admin.failed-jobs.index') }}" class="btn btn-outline-warning px-3 py-2 fw-semibold d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-octagon"></i> Failed Jobs ({{ $failedJobsCount }})
            </a>
        </div>
    </div>

    <!-- System Overview & Environment Info -->
    <div class="card card-premium border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-secondary border-opacity-20 py-3">
            <h5 class="card-title text-white fw-bold mb-0"><i class="bi bi-info-square me-2 text-primary"></i>System Architecture Overview</h5>
        </div>
        <div class="card-body">
            <div class="row g-4 text-center">
                <div class="col-md-3 col-6">
                    <div class="p-3 rounded bg-dark border border-secondary border-opacity-20">
                        <div class="text-muted small">Waypoint Version</div>
                        <div class="fs-5 fw-bold text-white">{{ $systemInfo['app_version'] }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="p-3 rounded bg-dark border border-secondary border-opacity-20">
                        <div class="text-muted small">Framework & PHP</div>
                        <div class="fs-5 fw-bold text-white">Laravel {{ $systemInfo['laravel_version'] }} / PHP {{ $systemInfo['php_version'] }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="p-3 rounded bg-dark border border-secondary border-opacity-20">
                        <div class="text-muted small">Environment</div>
                        <div class="fs-5 fw-bold text-success text-uppercase">{{ $systemInfo['environment'] }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="p-3 rounded bg-dark border border-secondary border-opacity-20">
                        <div class="text-muted small">Queue / Cache / DB</div>
                        <div class="fs-5 fw-bold text-info text-capitalize">{{ $systemInfo['queue_driver'] }} / {{ $systemInfo['cache_driver'] }} / {{ $systemInfo['database_driver'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Background Services Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card card-premium p-3 border-0 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Queue Status</span>
                    <span class="badge bg-success bg-opacity-20 text-success">{{ $queueStatus }}</span>
                </div>
                <div class="display-6 fw-bold text-white">{{ $pendingJobsCount }}</div>
                <div class="text-muted small">Pending Queue Jobs</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-premium p-3 border-0 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Failed Jobs Log</span>
                    <span class="badge bg-{{ $failedJobsCount > 0 ? 'danger' : 'success' }} bg-opacity-20 text-{{ $failedJobsCount > 0 ? 'danger' : 'success' }}">
                        {{ $failedJobsCount > 0 ? 'Action Required' : 'Clean' }}
                    </span>
                </div>
                <div class="display-6 fw-bold text-white">{{ $failedJobsCount }}</div>
                <div class="text-muted small">Failed Worker Tasks</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-premium p-3 border-0 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Scheduler Engine</span>
                    <span class="badge bg-primary bg-opacity-20 text-primary">Active</span>
                </div>
                <div class="display-6 fw-bold text-white">6</div>
                <div class="text-muted small">Scheduled Cron Tasks</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-premium p-3 border-0 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">API Log Records</span>
                    <span class="badge bg-info bg-opacity-20 text-info">Audited</span>
                </div>
                <div class="display-6 fw-bold text-white">{{ \App\Models\ApiLog::count() }}</div>
                <div class="text-muted small">Audited API Requests</div>
            </div>
        </div>
    </div>

    <!-- Master Synchronization Status Table -->
    <div class="card card-premium border-0 shadow-sm">
        <div class="card-header bg-transparent border-secondary border-opacity-20 py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title text-white fw-bold mb-0">Synchronization Metrics & Resource Usage</h5>
            <a href="{{ route('admin.sync.index') }}" class="btn btn-sm btn-outline-primary fw-semibold">Manage Sync Services</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0" style="background: transparent;">
                    <thead class="table-light table-group-divider small text-uppercase">
                        <tr>
                            <th class="ps-4">Service</th>
                            <th>Status</th>
                            <th>Last Sync</th>
                            <th>Next Scheduled</th>
                            <th>Duration</th>
                            <th>Memory / Peak</th>
                            <th>HTTP / Latency</th>
                            <th class="pe-4">Records</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($syncData as $key => $info)
                            <tr>
                                <td class="ps-4 fw-bold text-white text-capitalize">
                                    {{ $key }}
                                </td>
                                <td>
                                    <span class="badge bg-{{ $info['status'] === 'success' ? 'success' : ($info['status'] === 'failed' ? 'danger' : 'primary') }} bg-opacity-20 text-{{ $info['status'] === 'success' ? 'success' : ($info['status'] === 'failed' ? 'danger' : 'primary') }}">
                                        {{ ucfirst($info['status']) }}
                                    </span>
                                </td>
                                <td class="text-muted small">
                                    {{ $info['last_sync_at'] ? \Carbon\Carbon::parse($info['last_sync_at'])->diffForHumans() : 'Never' }}
                                </td>
                                <td class="font-monospace text-muted small">
                                    {{ $info['next_scheduled_at'] }}
                                </td>
                                <td class="font-monospace text-white small">
                                    {{ $info['duration_seconds'] ?? 0 }}s
                                </td>
                                <td class="font-monospace text-info small">
                                    {{ $info['memory_usage_mb'] ?? 0 }}MB / {{ $info['peak_memory_mb'] ?? 0 }}MB
                                </td>
                                <td class="font-monospace small">
                                    <span class="text-success">{{ $info['http_status'] ?? 200 }}</span> ({{ $info['api_latency_ms'] ?? 0 }}ms)
                                </td>
                                <td>
                                    <span class="fw-bold text-white">{{ number_format($info['records_updated'] ?? 0) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
