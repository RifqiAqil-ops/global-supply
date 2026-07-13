@extends('layouts.app')

@section('title', 'Audit Trails')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 text-white mb-1">Audit Trails</h1>
        <p class="text-muted small mb-0">Review system activity records, configuration logs, and administrative operations logs.</p>
    </div>
    <div>
        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20 py-2 px-3 fw-semibold">
            <i class="bi bi-shield-lock me-1"></i> Admin Privileges Enabled
        </span>
    </div>
</div>

<x-card title="System Operations Logs" icon="bi-journal-text">
    @if($logs->count() > 0)
    <div class="table-responsive">
        <x-table :headers="['Operator', 'Action Scope', 'Description & Operation Log', 'IP Address', 'Browser Agent', 'Logged At']">
            @foreach($logs as $log)
            @php
                $scopeColor = 'secondary';
                if ($log->action === 'risk_alert') $scopeColor = 'danger';
                elseif ($log->action === 'weights_update') $scopeColor = 'warning';
                elseif ($log->action === 'config_update') $scopeColor = 'info';
            @endphp
            <tr>
                <td class="align-middle">
                    @if($log->user)
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold bg-primary text-white text-xs" style="width: 24px; height: 24px; font-size: 0.7rem;">
                            {{ strtoupper(substr($log->user->name, 0, 2)) }}
                        </div>
                        <span class="text-white small fw-semibold">{{ $log->user->name }}</span>
                    </div>
                    @else
                    <span class="text-muted small"><i class="bi bi-robot me-1 text-info"></i>System Agent</span>
                    @endif
                </td>
                <td class="align-middle">
                    <span class="badge bg-{{ $scopeColor }} bg-opacity-10 text-{{ $scopeColor }} border border-{{ $scopeColor }} border-opacity-20 px-2 py-0.5 rounded small" style="font-size: 0.65rem; letter-spacing: 0.5px;">
                        {{ strtoupper(str_replace('_', ' ', $log->action)) }}
                    </span>
                </td>
                <td class="align-middle" style="max-width: 350px;">
                    <div class="text-white small fw-medium mb-1">{{ $log->description }}</div>
                    @if($log->old_values || $log->new_values)
                    <button class="btn btn-link text-primary p-0 m-0 border-0 fs-7 text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#audit-diff-{{ $log->id }}">
                        View details <i class="bi bi-chevron-down ms-0.5"></i>
                    </button>
                    <div class="collapse mt-2" id="audit-diff-{{ $log->id }}">
                        <div class="p-2.5 rounded bg-dark border border-secondary border-opacity-20 small font-monospace" style="font-size: 0.72rem; max-height: 150px; overflow-y: auto;">
                            @if($log->old_values)
                            <div class="text-danger mb-1">- OLD: {{ json_encode($log->old_values) }}</div>
                            @endif
                            @if($log->new_values)
                            <div class="text-success">+ NEW: {{ json_encode($log->new_values) }}</div>
                            @endif
                        </div>
                    </div>
                    @endif
                </td>
                <td class="align-middle small font-monospace text-muted">{{ $log->ip_address ?? '—' }}</td>
                <td class="align-middle small text-muted" style="max-width: 150px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;" title="{{ $log->user_agent }}">
                    {{ $log->user_agent ?? '—' }}
                </td>
                <td class="align-middle text-muted small" title="{{ $log->created_at }}">
                    {{ $log->created_at ? $log->created_at->diffForHumans() : '—' }}
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
        <i class="bi bi-journal-text display-4 text-muted d-block mb-3"></i>
        <h5 class="text-white">No Audit Log Found</h5>
        <p class="text-muted small">No operations logs or activity trails are currently recorded in the system.</p>
    </div>
    @endif
</x-card>
@endsection
