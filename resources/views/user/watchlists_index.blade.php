@extends('layouts.app')

@section('title', 'Sourcing Watchlists')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 text-white mb-1">Sourcing Watchlist</h1>
        <p class="text-muted small mb-0">Monitor custom risk alert thresholds for logistics, weather, and macro economics indicators.</p>
    </div>
    <div>
        <button class="btn btn-primary d-flex align-items-center gap-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalAddWatchlist">
            <i class="bi bi-plus-lg"></i> Add Country
        </button>
    </div>
</div>

<!-- Flash messages -->
@if(session('success'))
<div class="alert alert-success bg-success bg-opacity-10 text-success border border-success border-opacity-20 alert-dismissible fade show mb-4 small" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20 alert-dismissible fade show mb-4 small" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Search Filter -->
<div class="card card-premium border-0 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('watchlists.index') }}" class="row g-3">
            <div class="col-md-9">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-dark border-secondary border-opacity-40 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control bg-dark border-secondary border-opacity-40 text-white" placeholder="Search watchlist by country name, ISO2, or ISO3..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary px-3 fw-semibold w-100"><i class="bi bi-funnel me-1"></i> Filter</button>
                <a href="{{ route('watchlists.index') }}" class="btn btn-sm btn-secondary px-3 fw-semibold"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Main Table or Empty State -->
<x-card title="Monitored Sourcing Locations" icon="bi-eye">
    @if($items->count() > 0)
    <div class="table-responsive">
        <x-table :headers="['Country', 'Composite Risk', 'Alert Threshold', 'Status', 'Notes', 'Actions']">
            @foreach($items as $item)
            @php
                $score = $item->country->latestRiskScore;
                $riskVal = $score ? (float)$score->composite_score : null;
                $level = $score ? $score->risk_level : 'low';
                
                $badgeType = 'success';
                if ($level === 'high' || $level === 'critical') $badgeType = 'danger';
                elseif ($level === 'medium') $badgeType = 'warning';

                $isExceeded = $riskVal !== null && $riskVal >= (float)$item->alert_threshold;
                $statusText = $isExceeded ? 'Threshold Triggered' : 'Normal';
                $statusBadge = $isExceeded ? 'danger' : 'success';
            @endphp
            <tr>
                <td class="align-middle">
                    <a href="{{ route('countries.show', $item->country->iso2) }}" class="d-flex align-items-center gap-2 small text-white text-decoration-none hover-primary">
                        <img src="{{ $item->country->flag_url }}" alt="{{ $item->country->name }} Flag" class="rounded border border-secondary border-opacity-10" style="width: 24px; height: 16px; object-fit: cover;">
                        <strong>{{ $item->country->name }}</strong> <span class="text-muted">({{ $item->country->iso3 }})</span>
                    </a>
                </td>
                <td class="align-middle">
                    @if($riskVal !== null)
                    <x-badge type="{{ $badgeType }}">
                        {{ number_format($riskVal, 1) }} ({{ ucfirst($level) }})
                    </x-badge>
                    @else
                    <span class="text-muted small">N/A</span>
                    @endif
                </td>
                <td class="align-middle">
                    <span class="text-white fw-semibold">{{ number_format((float)$item->alert_threshold, 1) }}%</span>
                </td>
                <td class="align-middle">
                    <span class="badge bg-{{ $statusBadge }} bg-opacity-10 text-{{ $statusBadge }} border border-{{ $statusBadge }} border-opacity-20 px-2.5 py-1 rounded small fw-bold">
                        <i class="bi {{ $isExceeded ? 'bi-exclamation-triangle-fill' : 'bi-check-circle-fill' }} me-1"></i>
                        {{ $statusText }}
                    </span>
                </td>
                <td class="align-middle text-muted small" style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    {{ $item->notes ?? '—' }}
                </td>
                <td class="align-middle">
                    <div class="d-flex align-items-center gap-2">
                        <!-- Edit Button Trigger -->
                        <button class="btn btn-sm btn-outline-primary py-1 px-2.5 fs-7" 
                                data-bs-toggle="modal" 
                                data-bs-target="#modalEditWatchlist-{{ $item->id }}">
                            <i class="bi bi-pencil"></i> Edit
                        </button>
                        
                        <!-- Remove Button -->
                        <form action="{{ route('watchlists.destroy', $item->id) }}" method="POST" class="m-0" onsubmit="return confirm('Are you sure you want to remove {{ $item->country->name }} from your watchlist?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2.5 fs-7">
                                <i class="bi bi-trash"></i> Remove
                            </button>
                        </form>
                    </div>
                </td>
            </tr>

            <!-- Edit Watchlist Item Modal -->
            <div class="modal fade" id="modalEditWatchlist-{{ $item->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content bg-dark border-secondary border-opacity-35 text-white">
                        <div class="modal-header border-bottom border-secondary border-opacity-15">
                            <h5 class="modal-title fs-6 fw-bold"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Watchlist Threshold: {{ $item->country->name }}</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('watchlists.update', $item->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label text-muted small fw-semibold">Alert Threshold Score (0 - 100)</label>
                                    <input type="number" step="0.1" min="0" max="100" name="alert_threshold" class="form-control bg-dark border-secondary text-white" value="{{ (float)$item->alert_threshold }}" required>
                                    <div class="form-text text-muted small">You will receive system alerts if the country's composite risk score exceeds this value.</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted small fw-semibold">Sourcing Notes & Remarks</label>
                                    <textarea name="notes" class="form-control bg-dark border-secondary text-white" rows="3" placeholder="Add sourcing remarks, supplier lists, port hubs...">{{ $item->notes }}</textarea>
                                </div>
                            </div>
                            <div class="modal-footer border-top border-secondary border-opacity-15">
                                <button type="button" class="btn btn-sm btn-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-sm btn-primary px-3 fw-semibold">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </x-table>
    </div>
    
    @if($items->hasPages())
    <div class="card-footer bg-transparent border-top py-3" style="border-color: var(--color-border) !important;">
        {{ $items->appends(request()->query())->links() }}
    </div>
    @endif

    @else
    <!-- Premium Empty State -->
    <div class="text-center py-5">
        <div class="display-5 text-muted mb-3"><i class="bi bi-eye-slash-fill"></i></div>
        <h5 class="text-white">Watchlist is Empty</h5>
        <p class="text-muted small mx-auto mb-4" style="max-width: 360px;">No sourcing country profiles matched your filters. Click the button below to add custom locations to start tracking alert levels.</p>
        <button class="btn btn-sm btn-primary fw-semibold px-4 py-2" data-bs-toggle="modal" data-bs-target="#modalAddWatchlist">
            <i class="bi bi-plus-lg me-1"></i> Add Country to Watchlist
        </button>
    </div>
    @endif
</x-card>

<!-- Add Watchlist Item Modal -->
<div class="modal fade" id="modalAddWatchlist" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border-secondary border-opacity-35 text-white">
            <div class="modal-header border-bottom border-secondary border-opacity-15">
                <h5 class="modal-title fs-6 fw-bold"><i class="bi bi-plus-circle me-2 text-primary"></i>Add Country to Sourcing Watchlist</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('watchlists.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Select Country</label>
                        <select name="country_id" class="form-select bg-dark border-secondary text-white" required>
                            <option value="">-- Select Country --</option>
                            @foreach($allCountries as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->iso3 }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Alert Threshold Score (0 - 100)</label>
                        <input type="number" step="0.1" min="0" max="100" name="alert_threshold" class="form-control bg-dark border-secondary text-white" value="75.0" required>
                        <div class="form-text text-muted small">You will receive system alerts if the country's composite risk score exceeds this value.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Sourcing Notes & Remarks</label>
                        <textarea name="notes" class="form-control bg-dark border-secondary text-white" rows="3" placeholder="Add sourcing remarks, supplier lists, port hubs..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-15">
                    <button type="button" class="btn btn-sm btn-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary px-3 fw-semibold">Add Country</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
