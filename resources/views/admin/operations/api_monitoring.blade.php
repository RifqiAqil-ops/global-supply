@extends('layouts.app')

@section('title', 'API Monitoring & Health')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h3 text-white fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="bi bi-activity text-info"></i> API Performance & Monitoring Center
            </h1>
            <p class="text-muted small mb-0">
                Track response latency, HTTP status codes, quota health, and failure logs for all external data providers.
            </p>
        </div>
        <div>
            <a href="{{ route('admin.diagnose-api') }}" class="btn btn-primary px-3 py-2 fw-semibold d-flex align-items-center gap-2">
                <i class="bi bi-play-circle-fill"></i> Run Live API Diagnostics
            </a>
        </div>
    </div>

    <!-- Provider Cards Grid -->
    <div class="row g-4 mb-4">
        @foreach($summary as $key => $api)
        <div class="col-md-4">
            <div class="card card-premium p-4 border-0 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="text-white fw-bold mb-0">{{ $api['name'] }}</h5>
                    <span class="badge bg-{{ $api['status'] === 'Healthy' ? 'success' : 'warning' }} bg-opacity-20 text-{{ $api['status'] === 'Healthy' ? 'success' : 'warning' }} px-3 py-2">
                        {{ $api['status'] }}
                    </span>
                </div>
                <div class="row text-center mb-3 g-2">
                    <div class="col-4">
                        <div class="p-2 rounded bg-dark border border-secondary border-opacity-20">
                            <div class="text-muted small" style="font-size: 0.7rem;">Success</div>
                            <div class="text-success fw-bold">{{ number_format($api['success']) }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 rounded bg-dark border border-secondary border-opacity-20">
                            <div class="text-muted small" style="font-size: 0.7rem;">Failed</div>
                            <div class="text-danger fw-bold">{{ number_format($api['failed']) }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 rounded bg-dark border border-secondary border-opacity-20">
                            <div class="text-muted small" style="font-size: 0.7rem;">Latency</div>
                            <div class="text-info font-monospace fw-bold">{{ $api['avg_latency_ms'] }}ms</div>
                        </div>
                    </div>
                </div>
                <div class="small text-muted d-flex justify-content-between">
                    <span>Last Success: <strong class="text-white">{{ $api['last_success_at'] }}</strong></span>
                    <span>Failures: <strong class="text-white">{{ $api['last_failure_at'] }}</strong></span>
                </div>
                @if($api['last_error'])
                <div class="mt-2 p-2 rounded bg-danger bg-opacity-10 text-danger font-monospace small text-truncate" title="{{ $api['last_error'] }}">
                    {{ $api['last_error'] }}
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- Audited Logs Table -->
    <div class="card card-premium border-0 shadow-sm">
        <div class="card-header bg-transparent border-secondary border-opacity-20 py-3">
            <h5 class="card-title text-white fw-bold mb-0">Audited API Requests Log</h5>
        </div>
        <div class="card-body p-0">
            @if($logs->count() > 0)
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0" style="background: transparent;">
                    <thead class="table-light table-group-divider small text-uppercase">
                        <tr>
                            <th class="ps-4">Provider</th>
                            <th>Endpoint</th>
                            <th>HTTP Status</th>
                            <th>Response Time</th>
                            <th>Called At</th>
                            <th class="pe-4">Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            <tr>
                                <td class="ps-4 fw-bold text-white text-capitalize">
                                    {{ $log->provider }}
                                </td>
                                <td class="font-monospace small text-muted text-truncate" style="max-width: 320px;" title="{{ $log->endpoint }}">
                                    {{ $log->endpoint }}
                                </td>
                                <td class="font-monospace">
                                    <span class="badge bg-{{ $log->status_code >= 200 && $log->status_code < 300 ? 'success' : 'danger' }} bg-opacity-20 text-{{ $log->status_code >= 200 && $log->status_code < 300 ? 'success' : 'danger' }}">
                                        {{ $log->status_code ?? 200 }}
                                    </span>
                                </td>
                                <td class="font-monospace text-info small">
                                    {{ $log->response_time }} ms
                                </td>
                                <td class="text-muted small">
                                    {{ $log->called_at->format('Y-m-d H:i:s') }}
                                    <div class="text-muted small" style="font-size: 0.72rem;">{{ $log->called_at->diffForHumans() }}</div>
                                </td>
                                <td class="pe-4">
                                    @if($log->is_success)
                                        <span class="badge bg-success bg-opacity-20 text-success"><i class="bi bi-check me-1"></i> Success</span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-20 text-danger" title="{{ $log->error_message }}"><i class="bi bi-x me-1"></i> Failed</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $logs->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-info-circle display-4 text-muted d-block mb-3"></i>
                <h5 class="text-white">No Audited API Logs Found</h5>
                <p class="text-muted small mb-0">Run live diagnostics or execute background sync commands to populate API logs.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
