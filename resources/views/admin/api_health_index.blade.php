@extends('layouts.app')

@section('title', 'API Health Log')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 text-white mb-1">API Health Log</h1>
        <p class="text-muted small mb-0">Monitor external API transactions, response latency, and network connection logs.</p>
    </div>
    <div>
        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20 py-2 px-3 fw-semibold">
            <i class="bi bi-shield-lock me-1"></i> Admin Privileges Enabled
        </span>
    </div>
</div>

<!-- API Status metrics -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <x-stat-card 
            title="Total API Calls" 
            value="{{ number_format($totalCalls) }}" 
            icon="bi-activity" 
            iconColor="primary" 
        />
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-stat-card 
            title="Successful Requests" 
            value="{{ number_format($successfulCalls) }}" 
            icon="bi-check-circle" 
            iconColor="success" 
        />
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-stat-card 
            title="Failed Requests" 
            value="{{ number_format($failedCalls) }}" 
            icon="bi-x-circle" 
            iconColor="danger" 
        />
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-stat-card 
            title="Average Latency" 
            value="{{ $avgLatency }}ms" 
            icon="bi-speedometer" 
            iconColor="info" 
        />
    </div>
</div>

<x-card title="External API Transaction Logs" icon="bi-journal-code">
    @if($logs->count() > 0)
    <div class="table-responsive">
        <x-table :headers="['API Provider', 'Method & Endpoint', 'Status Code', 'Latency', 'Payload Size', 'Error Message', 'Time Logged']">
            @foreach($logs as $log)
            @php
                $statusBadge = $log->is_success ? 'success' : 'danger';
            @endphp
            <tr>
                <td class="align-middle"><strong>{{ $log->provider }}</strong></td>
                <td class="align-middle text-muted small" style="max-width: 200px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                    <span class="badge bg-secondary text-white px-2 py-0.5 rounded me-1" style="font-size: 0.65rem;">{{ $log->method }}</span>
                    <code>{{ parse_url($log->endpoint, PHP_URL_PATH) }}</code>
                </td>
                <td class="align-middle">
                    <span class="badge bg-{{ $statusBadge }} bg-opacity-10 text-{{ $statusBadge }} border border-{{ $statusBadge }} border-opacity-20 px-2.5 py-1 rounded small font-monospace fw-bold">
                        {{ $log->status_code ?? 'Timeout' }}
                    </span>
                </td>
                <td class="align-middle text-white small font-monospace">{{ round($log->response_time) }} ms</td>
                <td class="align-middle text-muted small">{{ number_format($log->response_size / 1024, 2) }} KB</td>
                <td class="align-middle text-danger small" style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $log->error_message }}">
                    {{ $log->error_message ?? '—' }}
                </td>
                <td class="align-middle text-muted small" title="{{ $log->called_at }}">
                    {{ $log->called_at ? $log->called_at->diffForHumans() : '—' }}
                </td>
            </tr>
            @endforeach
        </x-table>
    </div>
    
    @if($logs->hasPages())
    <div class="card-footer bg-transparent border-top py-3" style="border-color: var(--color-border) !important;">
        {{ $logs->links() }}
    </div>
    @endif

    @else
    <div class="text-center py-5">
        <i class="bi bi-cpu display-4 text-muted d-block mb-3"></i>
        <h5 class="text-white">No API Logs Found</h5>
        <p class="text-muted small">Run sync tasks or trigger queries to populate API transaction records.</p>
    </div>
    @endif
</x-card>
@endsection
