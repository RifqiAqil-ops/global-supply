@extends('layouts.app')

@section('title', 'Dataset Pelabuhan')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Area -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="typo-h1 mb-1">Commercial Ports Dataset</h1>
            <p class="text-muted small mb-0">Create, update, search, or delete global maritime logistics hubs.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <form action="{{ route('admin.ports.index') }}" method="GET" class="d-flex gap-2">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0" style="border-radius: 16px 0 0 16px;"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search ports..." value="{{ request('search') }}" style="border-radius: 0 16px 16px 0; width: 200px;">
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
                @if(request()->filled('search'))
                    <a href="{{ route('admin.ports.index') }}" class="btn btn-secondary-saas">Clear</a>
                @endif
            </form>
            <a href="{{ route('admin.ports.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle-fill"></i> Add Port
            </a>
        </div>
    </div>

    <!-- Ports Table Card -->
    <div class="card border-0 shadow-sm p-4" style="border-radius: 24px; border: 1px solid #E5E7EB !important; background-color: #FFFFFF;">
        @if($ports->count() > 0)
            <div class="table-responsive">
                <table class="table table-premium align-middle">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Port Name</th>
                            <th>Sovereign Country</th>
                            <th>Latitude</th>
                            <th>Longitude</th>
                            <th>Logistics Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ports as $port)
                            <tr>
                                <td><code class="text-primary fw-semibold">{{ $port->code }}</code></td>
                                <td class="fw-semibold text-dark">{{ $port->name }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($port->country && $port->country->flag_url)
                                            <img src="{{ $port->country->flag_url }}" class="country-flag-premium" alt="Flag">
                                        @endif
                                        <span class="text-dark">{{ $port->country->name ?? 'Unknown' }}</span>
                                    </div>
                                </td>
                                <td class="text-muted small">{{ number_format($port->latitude, 4) }}</td>
                                <td class="text-muted small">{{ number_format($port->longitude, 4) }}</td>
                                <td>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 px-2.5 py-1">
                                        {{ $port->status ?? 'Operational' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1.5">
                                        <a href="{{ route('admin.ports.edit', $port->id) }}" class="btn btn-sm btn-outline-primary" title="Edit Port Details">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.ports.destroy', $port->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this port record?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Port">
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

            @if($ports->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $ports->links() }}
                </div>
            @endif
        @else
            <div class="empty-state-card py-5">
                <div class="empty-state-icon"><i class="bi bi-anchor"></i></div>
                <h4 class="empty-state-title">No Ports Registered</h4>
                <p class="empty-state-desc">We couldn't find any operational maritime port records in the dataset.</p>
                <a href="{{ route('admin.ports.create') }}" class="btn btn-primary">Add New Port</a>
            </div>
        @endif
    </div>
</div>
@endsection
