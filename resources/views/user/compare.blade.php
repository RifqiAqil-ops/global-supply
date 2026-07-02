@extends('layouts.app')

@section('title', 'Country Comparison Engine')

@push('styles')
<style>
    .compare-card { transition: transform 0.2s ease; }
    .compare-card:hover { transform: translateY(-2px); }
    .metric-label { font-size: 0.68rem; text-transform: uppercase; letter-spacing: 1px; }
    .metric-value { font-size: 1.3rem; }
    .vs-divider { width: 2px; background: linear-gradient(180deg, transparent, var(--color-primary), transparent); min-height: 100%; }
</style>
@endpush

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 text-white mb-1">Country Comparison Engine</h1>
        <p class="text-muted small mb-0">Select 2 or more countries to compare economic, risk, weather, and currency metrics side-by-side.</p>
    </div>
</div>

<!-- Country Selector -->
<div class="card card-premium border-0 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('compare.index') }}" class="row g-3 align-items-end">
            <div class="col-md-9">
                <label class="form-label text-muted small mb-1">Select Countries to Compare</label>
                <select name="countries[]" id="countrySelector" class="form-select bg-dark border-secondary border-opacity-40 text-white" multiple style="height: 42px;">
                    @foreach($allCountries as $c)
                        <option value="{{ $c->iso2 }}" {{ in_array($c->iso2, $selectedCodes) ? 'selected' : '' }}>
                            {{ $c->name }} ({{ $c->iso2 }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100 fw-semibold">
                    <i class="bi bi-shuffle me-1"></i> Compare
                </button>
            </div>
        </form>
    </div>
</div>

@if($comparisonData->count() >= 2)
<!-- Comparison Cards -->
<div class="row g-4 mb-4">
    @foreach($comparisonData as $item)
    <div class="col-md-{{ 12 / min($comparisonData->count(), 4) }}">
        <div class="card card-premium border-0 h-100 compare-card">
            <div class="card-header bg-transparent border-bottom py-3" style="border-color: var(--color-border) !important;">
                <div class="d-flex align-items-center gap-2">
                    <img src="{{ $item['country']->flag_url }}" alt="{{ $item['country']->name }}" class="rounded" style="width: 28px; height: 18px; object-fit: cover;">
                    <h5 class="text-white mb-0 fs-6 fw-bold">{{ $item['country']->name }}</h5>
                    <span class="badge bg-secondary-subtle text-secondary small ms-auto">{{ $item['country']->iso3 }}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex flex-column gap-3">
                    <!-- GDP -->
                    <div>
                        <div class="metric-label text-muted fw-bold">GDP {{ $item['gdp_year'] ? "({$item['gdp_year']})" : '' }}</div>
                        <div class="metric-value text-white fw-bold">
                            @if($item['gdp'])
                                ${{ number_format((float)$item['gdp'] / 1e9, 2) }}B
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </div>
                    </div>

                    <!-- GDP per Capita -->
                    <div>
                        <div class="metric-label text-muted fw-bold">GDP Per Capita</div>
                        <div class="metric-value text-white fw-bold">
                            @if($item['gdp_per_capita'])
                                ${{ number_format((float)$item['gdp_per_capita'], 0) }}
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </div>
                    </div>

                    <!-- Inflation -->
                    <div>
                        <div class="metric-label text-muted fw-bold">Inflation {{ $item['inflation_year'] ? "({$item['inflation_year']})" : '' }}</div>
                        <div class="metric-value fw-bold {{ $item['inflation'] && (float)$item['inflation'] > 5 ? 'text-danger' : 'text-success' }}">
                            @if($item['inflation'])
                                {{ number_format((float)$item['inflation'], 2) }}%
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </div>
                    </div>

                    <!-- Population -->
                    <div>
                        <div class="metric-label text-muted fw-bold">Population</div>
                        <div class="metric-value text-white fw-bold">
                            {{ number_format($item['population']) }}
                        </div>
                    </div>

                    <hr class="border-secondary border-opacity-20 my-1">

                    <!-- Weather -->
                    <div>
                        <div class="metric-label text-muted fw-bold">Weather</div>
                        @if($item['weather'])
                            <div class="text-white small">
                                <span class="fw-bold">{{ $item['weather']->temperature }}°C</span> · 
                                💧 {{ $item['weather']->humidity }}% ·
                                💨 {{ $item['weather']->wind_speed }} km/h
                            </div>
                            <div class="text-muted small">{{ $item['weather']->weather_description }}</div>
                        @else
                            <span class="text-muted small">N/A</span>
                        @endif
                    </div>

                    <!-- Exchange Rate -->
                    <div>
                        <div class="metric-label text-muted fw-bold">Exchange Rate (1 USD)</div>
                        @if($item['exchange_rate'])
                            <div class="text-white fw-bold">
                                {{ number_format((float)$item['exchange_rate']->rate_to_usd, 4) }} {{ $item['exchange_rate']->currency_code }}
                            </div>
                            @if($item['exchange_rate']->change_percent)
                                <span class="small {{ (float)$item['exchange_rate']->change_percent >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ (float)$item['exchange_rate']->change_percent >= 0 ? '+' : '' }}{{ number_format((float)$item['exchange_rate']->change_percent, 2) }}%
                                </span>
                            @endif
                        @else
                            <span class="text-muted small">N/A</span>
                        @endif
                    </div>

                    <hr class="border-secondary border-opacity-20 my-1">

                    <!-- Risk Score -->
                    <div>
                        <div class="metric-label text-muted fw-bold">Composite Risk Score</div>
                        @if($item['risk_score'])
                            @php
                                $rs = $item['risk_score'];
                                $badgeType = 'success';
                                if ($rs->risk_level === 'high' || $rs->risk_level === 'critical') $badgeType = 'danger';
                                elseif ($rs->risk_level === 'medium') $badgeType = 'warning';
                            @endphp
                            <div class="d-flex align-items-center gap-2">
                                <span class="metric-value text-white fw-bold">{{ number_format($rs->composite_score, 2) }}</span>
                                <x-badge type="{{ $badgeType }}">{{ ucfirst($rs->risk_level) }}</x-badge>
                            </div>
                            <!-- Mini breakdown -->
                            @if($rs->details)
                            <div class="mt-2 d-flex flex-wrap gap-1">
                                @foreach($rs->details as $d)
                                <span class="badge bg-dark border border-secondary border-opacity-20 text-muted" style="font-size: 0.62rem;">
                                    {{ Str::limit($d->riskCategory->name ?? '', 8) }}: {{ number_format($d->category_score, 1) }}
                                </span>
                                @endforeach
                            </div>
                            @endif
                        @else
                            <span class="text-muted small">Not calculated</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top py-2" style="border-color: var(--color-border) !important;">
                <a href="{{ route('countries.show', $item['country']->iso2) }}" class="btn btn-sm btn-outline-primary w-100 fw-semibold">
                    <i class="bi bi-arrow-right me-1"></i> View Full Profile
                </a>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Charts Section -->
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-card title="GDP Trend Comparison (Billion USD)" icon="bi-graph-up">
            <canvas id="gdpTrendChart" height="280"></canvas>
        </x-card>
    </div>
    <div class="col-lg-6">
        <x-card title="Inflation Trend Comparison (%)" icon="bi-graph-down">
            <canvas id="inflationTrendChart" height="280"></canvas>
        </x-card>
    </div>
</div>

@else
    @if(count($selectedCodes) > 0 && $comparisonData->count() < 2)
        <div class="alert alert-warning d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-triangle"></i>
            Please select at least 2 countries to enable comparison.
        </div>
    @else
        <div class="card card-premium border-0">
            <div class="card-body text-center py-5">
                <i class="bi bi-shuffle display-4 text-muted d-block mb-3"></i>
                <h5 class="text-white">Select Countries to Begin Comparison</h5>
                <p class="text-muted small mb-0">Use the selector above to pick 2 or more countries, then click Compare.</p>
            </div>
        </div>
    @endif
@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartColors = [
        'rgba(59,130,246,1)', 'rgba(239,68,68,1)', 'rgba(34,197,94,1)',
        'rgba(168,85,247,1)', 'rgba(249,115,22,1)', 'rgba(14,165,233,1)'
    ];
    const chartColorsBg = [
        'rgba(59,130,246,0.1)', 'rgba(239,68,68,0.1)', 'rgba(34,197,94,0.1)',
        'rgba(168,85,247,0.1)', 'rgba(249,115,22,0.1)', 'rgba(14,165,233,0.1)'
    ];

    const defaultOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { labels: { color: '#94a3b8', font: { size: 11 } } }
        },
        scales: {
            x: { ticks: { color: '#64748b', font: { size: 10 } }, grid: { color: 'rgba(100,116,139,0.1)' } },
            y: { ticks: { color: '#64748b', font: { size: 10 } }, grid: { color: 'rgba(100,116,139,0.1)' } }
        }
    };

    // GDP Trend
    const gdpTrends = @json($gdpTrends ?? []);
    if (gdpTrends.length > 0 && document.getElementById('gdpTrendChart')) {
        const allYears = [...new Set(gdpTrends.flatMap(t => t.years))].sort();
        new Chart(document.getElementById('gdpTrendChart'), {
            type: 'line',
            data: {
                labels: allYears,
                datasets: gdpTrends.map((t, i) => ({
                    label: t.label,
                    data: allYears.map(y => {
                        const idx = t.years.indexOf(y);
                        return idx >= 0 ? t.data[idx] : null;
                    }),
                    borderColor: chartColors[i % chartColors.length],
                    backgroundColor: chartColorsBg[i % chartColorsBg.length],
                    fill: true,
                    tension: 0.3,
                    pointRadius: 3,
                }))
            },
            options: defaultOptions
        });
    }

    // Inflation Trend
    const inflationTrends = @json($inflationTrends ?? []);
    if (inflationTrends.length > 0 && document.getElementById('inflationTrendChart')) {
        const allYears2 = [...new Set(inflationTrends.flatMap(t => t.years))].sort();
        new Chart(document.getElementById('inflationTrendChart'), {
            type: 'line',
            data: {
                labels: allYears2,
                datasets: inflationTrends.map((t, i) => ({
                    label: t.label,
                    data: allYears2.map(y => {
                        const idx = t.years.indexOf(y);
                        return idx >= 0 ? t.data[idx] : null;
                    }),
                    borderColor: chartColors[i % chartColors.length],
                    backgroundColor: chartColorsBg[i % chartColorsBg.length],
                    fill: true,
                    tension: 0.3,
                    pointRadius: 3,
                }))
            },
            options: defaultOptions
        });
    }
});
</script>
@endpush
