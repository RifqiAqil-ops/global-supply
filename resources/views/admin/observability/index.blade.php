@extends('layouts.app')

@section('title', 'Enterprise Observability Dashboard')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h3 text-white fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="bi bi-speedometer text-primary"></i> Enterprise Observability Center
            </h1>
            <p class="text-muted small mb-0">
                Real-time telemetry metrics: Success rate, throughput, database & cache latencies, system memory, and queue stats.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.health.index') }}" class="btn btn-outline-success px-3 py-2 fw-semibold d-flex align-items-center gap-2">
                <i class="bi bi-heart-pulse-fill"></i> Health Check
            </a>
            <a href="{{ route('admin.api-monitoring.index') }}" class="btn btn-outline-info px-3 py-2 fw-semibold d-flex align-items-center gap-2">
                <i class="bi bi-activity"></i> API Monitoring
            </a>
        </div>
    </div>

    <!-- Key Metrics Telemetry Grid -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card card-premium p-3 border-0 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Success Rate</span>
                    <span class="badge bg-success bg-opacity-20 text-success"><i class="bi bi-graph-up-arrow me-1"></i> SLA 99.9%</span>
                </div>
                <div class="display-6 fw-bold text-success">{{ $successRate }}%</div>
                <div class="text-muted small">Sync Success Index</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-premium p-3 border-0 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Average Sync Latency</span>
                    <span class="badge bg-info bg-opacity-20 text-info"><i class="bi bi-clock me-1"></i> Fast</span>
                </div>
                <div class="display-6 fw-bold text-white">{{ $avgSyncDuration }}s</div>
                <div class="text-muted small">Avg Duration Per Service</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-premium p-3 border-0 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Database Query Latency</span>
                    <span class="badge bg-primary bg-opacity-20 text-primary">MySQL</span>
                </div>
                <div class="display-6 fw-bold text-info">{{ $dbLatency }}ms</div>
                <div class="text-muted small">DB Ping Response Time</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-premium p-3 border-0 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Memory Consumption</span>
                    <span class="badge bg-warning bg-opacity-20 text-warning">Allocated</span>
                </div>
                <div class="display-6 fw-bold text-warning">{{ $memoryUsage }}MB</div>
                <div class="text-muted small">Peak: {{ $peakMemory }}MB</div>
            </div>
        </div>
    </div>

    <!-- Telemetry System Panels -->
    <div class="row g-4 mb-4">
        <!-- Subsystems Latency Panel -->
        <div class="col-md-6">
            <div class="card card-premium border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-secondary border-opacity-20 py-3">
                    <h5 class="card-title text-white fw-bold mb-0"><i class="bi bi-cpu me-2 text-primary"></i>Subsystem Performance Telemetry</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between text-muted small mb-1">
                            <span>Database Ping Latency</span>
                            <span class="text-white fw-bold">{{ $dbLatency }} ms</span>
                        </div>
                        <div class="progress bg-dark" style="height: 8px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ min(100, $dbLatency * 5) }}%;"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between text-muted small mb-1">
                            <span>Cache Write/Read Latency</span>
                            <span class="text-white fw-bold">{{ $cacheLatency }} ms</span>
                        </div>
                        <div class="progress bg-dark" style="height: 8px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ min(100, $cacheLatency * 10) }}%;"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between text-muted small mb-1">
                            <span>Memory Utilization vs Peak</span>
                            <span class="text-white fw-bold">{{ $memoryUsage }} MB / {{ $peakMemory }} MB</span>
                        </div>
                        <div class="progress bg-dark" style="height: 8px;">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ min(100, ($memoryUsage / max(1, $peakMemory)) * 100) }}%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Infrastructure Storage & Queue Metrics -->
        <div class="col-md-6">
            <div class="card card-premium border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-secondary border-opacity-20 py-3">
                    <h5 class="card-title text-white fw-bold mb-0"><i class="bi bi-hdd-network me-2 text-info"></i>Infrastructure & Disk Space</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 text-center mb-3">
                        <div class="col-6">
                            <div class="p-3 rounded bg-dark border border-secondary border-opacity-20">
                                <div class="text-muted small">Available Free Disk</div>
                                <div class="fs-4 fw-bold text-success">{{ $freeDisk }} GB</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded bg-dark border border-secondary border-opacity-20">
                                <div class="text-muted small">Total Volume Space</div>
                                <div class="fs-4 fw-bold text-white">{{ $totalDisk }} GB</div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center text-muted small p-2 rounded bg-dark">
                        <span>Pending Jobs in Queue: <strong class="text-white">{{ $pendingJobs }}</strong></span>
                        <span>Failed Jobs Log: <strong class="text-{{ $failedJobs > 0 ? 'danger' : 'success' }}">{{ $failedJobs }}</strong></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
