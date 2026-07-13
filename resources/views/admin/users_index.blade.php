@extends('layouts.app')

@section('title', 'User Manager')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 text-white mb-1">User Manager</h1>
        <p class="text-muted small mb-0">Monitor registered user profiles, account creation details, and system roles.</p>
    </div>
    <div>
        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20 py-2 px-3 fw-semibold">
            <i class="bi bi-shield-lock me-1"></i> Admin Privileges Enabled
        </span>
    </div>
</div>

<x-card title="Registered User Accounts" icon="bi-people">
    @if($users->count() > 0)
    <div class="table-responsive">
        <x-table :headers="['ID', 'User Name', 'Email Address', 'Role', 'Registered At']">
            @foreach($users as $user)
            @php
                $roleBadge = $user->role === 'admin' ? 'danger' : 'primary';
            @endphp
            <tr>
                <td class="align-middle text-muted small">#{{ $user->id }}</td>
                <td class="align-middle">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold bg-primary text-white text-xs" style="width: 28px; height: 28px; font-size: 0.75rem;">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        <strong class="text-white">{{ $user->name }}</strong>
                    </div>
                </td>
                <td class="align-middle text-white small">{{ $user->email }}</td>
                <td class="align-middle">
                    <span class="badge bg-{{ $roleBadge }} bg-opacity-10 text-{{ $roleBadge }} border border-{{ $roleBadge }} border-opacity-20 px-2.5 py-1 rounded small text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">
                        {{ $user->role }}
                    </span>
                </td>
                <td class="align-middle text-muted small">
                    {{ $user->created_at ? $user->created_at->format('M d, Y H:i A') : '—' }} ({{ $user->created_at ? $user->created_at->diffForHumans() : '—' }})
                </td>
            </tr>
            @endforeach
        </x-table>
    </div>
    
    @if($users->hasPages())
    <div class="card-footer bg-transparent border-top py-3" style="border-color: var(--color-border) !important;">
        {{ $users->links() }}
    </div>
    @endif

    @else
    <div class="text-center py-5">
        <i class="bi bi-people display-4 text-muted d-block mb-3"></i>
        <h5 class="text-white">No Users Found</h5>
        <p class="text-muted small">No registered user profiles are present in the system.</p>
    </div>
    @endif
</x-card>
@endsection
