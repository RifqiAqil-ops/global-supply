@extends('layouts.app')

@section('title', 'Automated Data Synchronization')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h3 text-white fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="bi bi-arrow-repeat text-primary"></i> Data Synchronization Manager
            </h1>
            <p class="text-muted small mb-0">
                Monitor background automated schedules, track sync duration metrics, and trigger manual dataset refreshes.
            </p>
        </div>
        <div class="d-flex gap-2">
            <form action="{{ route('admin.sync.run-all') }}" method="POST" onsubmit="return confirm('Run full system setup and sync all datasets?')">
                @csrf
                <button type="submit" class="btn btn-primary px-3 py-2 fw-semibold d-flex align-items-center gap-2">
                    <i class="bi bi-lightning-charge-fill"></i> Run Full Setup & Sync All
                </button>
            </form>
        </div>
    </div>

    <!-- Active Scheduler Status Summary Card -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card card-premium p-3 border-0 h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle p-3 bg-primary bg-opacity-10 text-primary fs-4">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Laravel Scheduler</div>
                        <div class="fs-5 fw-bold text-white">Active</div>
                        <span class="badge bg-success bg-opacity-20 text-success small">Automated</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-premium p-3 border-0 h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle p-3 bg-info bg-opacity-10 text-info fs-4">
                        <i class="bi bi-cpu"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Background Queues</div>
                        <div class="fs-5 fw-bold text-white">Non-blocking</div>
                        <span class="badge bg-info bg-opacity-20 text-info small">Async Drivers</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-premium p-3 border-0 h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle p-3 bg-warning bg-opacity-10 text-warning fs-4">
                        <i class="bi bi-database-check"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Master Datasets</div>
                        <div class="fs-5 fw-bold text-white">7 Services</div>
                        <span class="badge bg-warning bg-opacity-20 text-warning small">Local Cached</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-premium p-3 border-0 h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle p-3 bg-success bg-opacity-10 text-success fs-4">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div>
                        <div class="text-muted small">UI Load Latency</div>
                        <div class="fs-5 fw-bold text-white">&lt; 10ms</div>
                        <span class="badge bg-success bg-opacity-20 text-success small">Zero Blocking</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sync Services Table -->
    <div class="card card-premium border-0 shadow-sm">
        <div class="card-header bg-transparent border-secondary border-opacity-20 py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title text-white fw-bold mb-0">Synchronization Services Registry</h5>
            <span class="text-muted small"><i class="bi bi-info-circle me-1"></i> Data reads directly from local database</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0" style="background: transparent;">
                    <thead class="table-light table-group-divider small text-uppercase">
                        <tr>
                            <th class="ps-4">Service</th>
                            <th>Schedule Interval</th>
                            <th>Status</th>
                            <th>Last Sync Time</th>
                            <th>Next Scheduled</th>
                            <th>Records Processed</th>
                            <th>Duration</th>
                            <th class="pe-4 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $servicesMap = [
                                'countries' => ['name' => 'REST Countries Master API', 'icon' => 'bi-globe-americas', 'interval' => 'Weekly'],
                                'ports' => ['name' => 'World Port Index (UN/LOCODE)', 'icon' => 'bi-geo-alt', 'interval' => 'On Setup / Manual'],
                                'exchange' => ['name' => 'ExchangeRate Currency API', 'icon' => 'bi-currency-exchange', 'interval' => 'Every 1 Hour'],
                                'weather' => ['name' => 'Open-Meteo Weather API', 'icon' => 'bi-cloud-sun', 'interval' => 'Every 30 Mins'],
                                'worldbank' => ['name' => 'World Bank Macro Indicators', 'icon' => 'bi-bank', 'interval' => 'Daily'],
                                'news' => ['name' => 'GNews Geopolitical Feed', 'icon' => 'bi-newspaper', 'interval' => 'Every 1 Hour'],
                                'risk' => ['name' => 'Risk Scoring Calculation Engine', 'icon' => 'bi-speedometer2', 'interval' => 'Every 1 Hour'],
                            ];
                        @endphp

                        @foreach($servicesMap as $key => $meta)
                            @php
                                $info = $syncData[$key] ?? [];
                                $status = $info['status'] ?? 'pending';
                                $lastSync = $info['last_sync_at'] ? \Carbon\Carbon::parse($info['last_sync_at'])->diffForHumans() : 'Never';
                                $records = $info['records_updated'] ?? 0;
                                $duration = isset($info['duration_seconds']) ? $info['duration_seconds'] . 's' : '-';
                                $nextSched = $info['next_scheduled_at'] ?? 'Automated';
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="p-2 rounded bg-dark border border-secondary border-opacity-20 text-primary fs-5">
                                            <i class="bi {{ $meta['icon'] }}"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-white">{{ $meta['name'] }}</div>
                                            <div class="font-monospace text-muted small" style="font-size: 0.75rem;">Key: sync_info_{{ $key }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-dark border border-secondary text-secondary font-monospace small">
                                        {{ $meta['interval'] }}
                                    </span>
                                </td>
                                <td>
                                    @if($status === 'success')
                                        <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-30">
                                            <i class="bi bi-check-circle me-1"></i> Success
                                        </span>
                                    @elseif($status === 'failed')
                                        <span class="badge bg-danger bg-opacity-20 text-danger border border-danger border-opacity-30" title="{{ $info['error_message'] ?? '' }}">
                                            <i class="bi bi-exclamation-triangle me-1"></i> Failed
                                        </span>
                                    @elseif($status === 'syncing')
                                        <span class="badge bg-primary bg-opacity-20 text-primary border border-primary border-opacity-30">
                                            <span class="spinner-border spinner-border-sm me-1" role="status"></span> Syncing...
                                        </span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-20 text-secondary border border-secondary border-opacity-30">
                                            <i class="bi bi-dash-circle me-1"></i> Pending
                                        </span>
                                    @endif
                                </td>
                                <td class="text-white small">
                                    {{ $info['last_sync_at'] ? \Carbon\Carbon::parse($info['last_sync_at'])->format('Y-m-d H:i:s') : 'Not Synced' }}
                                    <div class="text-muted small" style="font-size: 0.75rem;">{{ $lastSync }}</div>
                                </td>
                                <td class="text-muted small font-monospace">
                                    {{ $nextSched }}
                                </td>
                                <td>
                                    <span class="fw-bold text-white">{{ number_format($records) }}</span>
                                    <span class="text-muted small">records</span>
                                </td>
                                <td class="font-monospace text-white small">
                                    {{ $duration }}
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="btn-group">
                                        <form action="{{ route('admin.sync.run') }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="service" value="{{ $key }}">
                                            <input type="hidden" name="mode" value="sync">
                                            <button type="submit" class="btn btn-sm btn-outline-primary fw-semibold" title="Run synchronously">
                                                <i class="bi bi-play-fill me-1"></i> Sync Now
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.sync.run') }}" method="POST" class="d-inline ms-1">
                                            @csrf
                                            <input type="hidden" name="service" value="{{ $key }}">
                                            <input type="hidden" name="mode" value="queue">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary fw-semibold" title="Dispatch background queue job">
                                                <i class="bi bi-sign-post-split me-1"></i> Queue
                                            </button>
                                        </form>
                                    </div>
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
