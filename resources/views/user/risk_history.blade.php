@extends('layouts.app')

@section('title', 'Global Risk Scoring History')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 text-white mb-1">Global Risk Scoring History</h1>
        <p class="text-muted small mb-0">Audit log database of all automated and manual country composite risk evaluations.</p>
    </div>
</div>

<!-- Filters Card -->
<div class="card card-premium border-0 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('risk-history.index') }}" class="row g-3">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-dark border-secondary border-opacity-40 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control bg-dark border-secondary border-opacity-40 text-white" placeholder="Search country by name or ISO code..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="risk_level" class="form-select form-select-sm bg-dark border-secondary border-opacity-40 text-white">
                    <option value="">-- All Risk Levels --</option>
                    <option value="low" {{ request('risk_level') === 'low' ? 'selected' : '' }}>Low Risk</option>
                    <option value="medium" {{ request('risk_level') === 'medium' ? 'selected' : '' }}>Medium Risk</option>
                    <option value="high" {{ request('risk_level') === 'high' ? 'selected' : '' }}>High Risk</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary px-3 fw-semibold">
                    <i class="bi bi-funnel me-1"></i> Apply Filters
                </button>
                <a href="{{ route('risk-history.index') }}" class="btn btn-sm btn-secondary px-3 fw-semibold">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- History Table Card -->
<x-card title="Risk Scoring History Entries" icon="bi-clock-history">
    <div class="table-responsive">
        <x-table :headers="['Country', 'Composite Score', 'Risk Level', 'Score Change', 'Weight Breakdown & Categories', 'Calculated At']">
            @forelse($history as $item)
            @php
                $level = $item->risk_level;
                $badgeType = 'success';
                if ($level === 'high' || $level === 'critical') $badgeType = 'danger';
                elseif ($level === 'medium') $badgeType = 'warning';

                $change = (float)$item->score_change;
                $changeColor = $change > 0 ? 'text-danger' : ($change < 0 ? 'text-success' : 'text-muted');
                $changeIcon = $change > 0 ? 'bi-arrow-up' : ($change < 0 ? 'bi-arrow-down' : 'bi-dash');
                
                // Group details by category slug
                $details = $item->details->keyBy('riskCategory.slug');
                
                // Helper closure to compute weight percentage
                $getWeightPercent = function($slug) use ($details) {
                    $detail = $details->get($slug);
                    if (!$detail) return '0%';
                    $catScore = (float)$detail->category_score;
                    $weighted = (float)$detail->weighted_score;
                    if ($catScore === 0.0) return '0%';
                    return round(($weighted / $catScore) * 100) . '%';
                };
            @endphp
            <tr>
                <td class="align-middle">
                    <a href="{{ route('countries.show', $item->country->iso2) }}" class="d-flex align-items-center gap-2 small text-white text-decoration-none hover-primary">
                        <img src="{{ $item->country->flag_url }}" alt="{{ $item->country->name }} Flag" class="rounded border border-secondary border-opacity-10" style="width: 20px; height: 13px; object-fit: cover;">
                        <strong>{{ $item->country->name }}</strong>
                    </a>
                </td>
                <td class="align-middle"><strong class="text-white fs-6">{{ number_format($item->composite_score, 2) }}</strong></td>
                <td class="align-middle"><x-badge type="{{ $badgeType }}">{{ ucfirst($level) }}</x-badge></td>
                <td class="align-middle">
                    <span class="{{ $changeColor }} fw-semibold small">
                        <i class="bi {{ $changeIcon }} me-0.5"></i>{{ $change > 0 ? '+' : '' }}{{ number_format($change, 2) }}
                    </span>
                </td>
                <td class="align-middle py-3">
                    <div class="d-flex flex-wrap gap-2 small">
                        @foreach(['economic-risk' => 'Econ', 'weather-risk' => 'Weather', 'currency-stability-risk' => 'Curr', 'geopolitical-risk' => 'Geo', 'logistics-risk' => 'Log'] as $slug => $label)
                            @php
                                $detail = $details->get($slug);
                            @endphp
                            @if($detail)
                            <div class="px-2.5 py-1.5 rounded-3 d-flex flex-column align-items-center" style="min-width: 68px; line-height: 1.25; background: #F8FAFC; border: 1px solid #E2E8F0;">
                                <span class="text-secondary fw-semibold" style="font-size: 0.65rem; letter-spacing: 0.5px; text-transform: uppercase;">{{ $label }}</span>
                                <span class="fw-extrabold my-0.5" style="font-size: 0.8rem; color: #0F172A;">{{ number_format($detail->category_score, 1) }}</span>
                                <span class="text-muted" style="font-size: 0.62rem;">w: {{ $getWeightPercent($slug) }}</span>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </td>
                <td class="align-middle small text-muted">
                    <span class="d-block text-white" style="font-size: 0.78rem;">{{ $item->calculated_at->format('M d, Y') }}</span>
                    <span style="font-size: 0.72rem;">{{ $item->calculated_at->format('h:i A') }} ({{ $item->calculated_at->diffForHumans() }})</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center text-muted py-5">
                    <i class="bi bi-clock display-6 d-block mb-3 text-secondary"></i>
                    <h6 class="text-white">No Risk Score History Records Found</h6>
                    <p class="small text-muted mb-0">Run calculation or adjust weights to populate historical entries.</p>
                </td>
            </tr>
            @endforelse
        </x-table>
    </div>
    
    @if($history->hasPages())
    <div class="mt-4 border-top border-secondary border-opacity-10 pt-3">
        {{ $history->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
    @endif
</x-card>
@endsection
