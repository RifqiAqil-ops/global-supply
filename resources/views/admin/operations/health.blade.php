@extends('layouts.app')

@section('title', 'System Health Monitoring')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h3 text-white fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="bi bi-heart-pulse text-success"></i> System Health Monitoring
            </h1>
            <p class="text-muted small mb-0">
                Real-time diagnostic health check for database, cache, queue workers, file storage, internet connectivity, and external APIs.
            </p>
        </div>
        <div>
            <button onclick="window.location.reload()" class="btn btn-outline-success px-3 py-2 fw-semibold d-flex align-items-center gap-2">
                <i class="bi bi-arrow-clockwise"></i> Re-run Health Diagnostics
            </button>
        </div>
    </div>

    <!-- Core Subsystems Health Grid -->
    <div class="row g-4 mb-4">
        <!-- Database -->
        <div class="col-md-4">
            <div class="card card-premium p-4 border-0 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-database fs-4 text-primary"></i>
                        <h5 class="text-white fw-bold mb-0">Database</h5>
                    </div>
                    <span class="badge bg-{{ $health['database']['badge'] }} bg-opacity-20 text-{{ $health['database']['badge'] }} px-3 py-2">
                        {{ $health['database']['status'] }}
                    </span>
                </div>
                <div class="text-muted small mb-2">MySQL Connection Status</div>
                <div class="font-monospace text-white fs-5 fw-bold">
                    {{ isset($health['database']['latency_ms']) ? $health['database']['latency_ms'] . ' ms' : 'Error' }}
                </div>
            </div>
        </div>

        <!-- Cache -->
        <div class="col-md-4">
            <div class="card card-premium p-4 border-0 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-lightning fs-4 text-warning"></i>
                        <h5 class="text-white fw-bold mb-0">Cache Engine</h5>
                    </div>
                    <span class="badge bg-{{ $health['cache']['badge'] }} bg-opacity-20 text-{{ $health['cache']['badge'] }} px-3 py-2">
                        {{ $health['cache']['status'] }}
                    </span>
                </div>
                <div class="text-muted small mb-2">Read/Write Latency</div>
                <div class="font-monospace text-white fs-5 fw-bold">
                    {{ isset($health['cache']['latency_ms']) ? $health['cache']['latency_ms'] . ' ms' : 'Error' }}
                </div>
            </div>
        </div>

        <!-- Queue -->
        <div class="col-md-4">
            <div class="card card-premium p-4 border-0 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-layers fs-4 text-info"></i>
                        <h5 class="text-white fw-bold mb-0">Queue Worker</h5>
                    </div>
                    <span class="badge bg-{{ $health['queue']['badge'] }} bg-opacity-20 text-{{ $health['queue']['badge'] }} px-3 py-2">
                        {{ $health['queue']['status'] }}
                    </span>
                </div>
                <div class="text-muted small mb-2">Pending Jobs in Queue</div>
                <div class="font-monospace text-white fs-5 fw-bold">
                    {{ $health['queue']['pending_jobs'] ?? 0 }} Jobs
                </div>
            </div>
        </div>

        <!-- Scheduler -->
        <div class="col-md-4">
            <div class="card card-premium p-4 border-0 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-clock-history fs-4 text-primary"></i>
                        <h5 class="text-white fw-bold mb-0">Task Scheduler</h5>
                    </div>
                    <span class="badge bg-{{ $health['scheduler']['badge'] }} bg-opacity-20 text-{{ $health['scheduler']['badge'] }} px-3 py-2">
                        {{ $health['scheduler']['status'] }}
                    </span>
                </div>
                <div class="text-muted small mb-2">Cron Registration</div>
                <div class="text-white font-monospace small">
                    {{ $health['scheduler']['info'] }}
                </div>
            </div>
        </div>

        <!-- Storage -->
        <div class="col-md-4">
            <div class="card card-premium p-4 border-0 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-folder-check fs-4 text-success"></i>
                        <h5 class="text-white fw-bold mb-0">Storage & Cache</h5>
                    </div>
                    <span class="badge bg-{{ $health['storage']['badge'] }} bg-opacity-20 text-{{ $health['storage']['badge'] }} px-3 py-2">
                        {{ $health['storage']['status'] }}
                    </span>
                </div>
                <div class="text-muted small mb-2">File Permissions</div>
                <div class="text-white font-monospace small">
                    {{ $health['storage']['info'] }}
                </div>
            </div>
        </div>

        <!-- Internet Connectivity -->
        <div class="col-md-4">
            <div class="card card-premium p-4 border-0 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-wifi fs-4 text-info"></i>
                        <h5 class="text-white fw-bold mb-0">Internet Outreach</h5>
                    </div>
                    <span class="badge bg-{{ $health['internet']['badge'] }} bg-opacity-20 text-{{ $health['internet']['badge'] }} px-3 py-2">
                        {{ $health['internet']['status'] }}
                    </span>
                </div>
                <div class="text-muted small mb-2">External DNS Latency</div>
                <div class="font-monospace text-white fs-5 fw-bold">
                    {{ isset($health['internet']['latency_ms']) ? $health['internet']['latency_ms'] . ' ms' : 'Offline' }}
                </div>
            </div>
        </div>
    </div>

    <!-- External API Diagnostics Table -->
    <div class="card card-premium border-0 shadow-sm">
        <div class="card-header bg-transparent border-secondary border-opacity-20 py-3">
            <h5 class="card-title text-white fw-bold mb-0"><i class="bi bi-globe me-2 text-primary"></i>External API Integrations Health</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0" style="background: transparent;">
                    <thead class="table-light table-group-divider small text-uppercase">
                        <tr>
                            <th class="ps-4">API Provider</th>
                            <th>Status</th>
                            <th>Response Latency</th>
                            <th class="pe-4">Last Called</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($apiHealth as $key => $api)
                            <tr>
                                <td class="ps-4 fw-bold text-white">
                                    {{ $api['name'] }}
                                </td>
                                <td>
                                    <span class="badge bg-{{ $api['status'] === 'Healthy' ? 'success' : 'warning' }} bg-opacity-20 text-{{ $api['status'] === 'Healthy' ? 'success' : 'warning' }}">
                                        {{ $api['status'] }}
                                    </span>
                                </td>
                                <td class="font-monospace text-white">
                                    {{ $api['latency_ms'] }} ms
                                </td>
                                <td class="text-muted small pe-4">
                                    {{ $api['last_called'] }}
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
