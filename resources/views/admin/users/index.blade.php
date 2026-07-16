@extends('layouts.app')

@section('title', 'User Manager')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Area -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="typo-h1 mb-1">User Manager</h1>
            <p class="text-muted small mb-0">Create, update, delete, or assign roles to platform operators.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus-fill"></i> Create Operator
            </a>
        </div>
    </div>

    <!-- Users Card -->
    <div class="card border-0 shadow-sm p-4" style="border-radius: 24px; border: 1px solid #E5E7EB !important; background-color: #FFFFFF;">
        @if($users->count() > 0)
            <div class="table-responsive">
                <table class="table table-premium align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Operator Name</th>
                            <th>Email Address</th>
                            <th>Role</th>
                            <th>Registered</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            @php
                                $roleBadge = $user->role === 'admin' ? 'danger' : 'primary';
                            @endphp
                            <tr>
                                <td class="text-muted small">#{{ $user->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center fw-bold text-dark text-xs" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </div>
                                        <strong class="text-dark">{{ $user->name }}</strong>
                                    </div>
                                </td>
                                <td class="text-dark">{{ $user->email }}</td>
                                <td>
                                    <span class="badge bg-{{ $roleBadge }} bg-opacity-10 text-{{ $roleBadge }} border border-{{ $roleBadge }} border-opacity-20 px-2 py-1 small text-uppercase" style="font-size: 0.68rem;">
                                        {{ $user->role }}
                                    </span>
                                </td>
                                <td class="text-muted small">
                                    {{ $user->created_at ? $user->created_at->format('M d, Y') : '—' }}
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1.5">
                                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-outline-primary" title="Edit Profile">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @if($user->id !== auth()->id())
                                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Operator">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <button class="btn btn-sm btn-outline-secondary" disabled title="Cannot delete self">
                                                <i class="bi bi-slash-circle"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $users->links() }}
                </div>
            @endif
        @else
            <div class="empty-state-card py-5">
                <div class="empty-state-icon"><i class="bi bi-people"></i></div>
                <h4 class="empty-state-title">No Operators Found</h4>
                <p class="empty-state-desc">There are no operational user records in the system database.</p>
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Create Operator</a>
            </div>
        @endif
    </div>
</div>
@endsection
