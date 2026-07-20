@extends('layouts.app')

@section('title', 'Currency Impact Dashboard')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 text-white mb-1">Currency Impact Dashboard</h1>
        <p class="text-muted small mb-0">Real-time exchange rate monitoring, daily movers analysis, and currency trend visualization.</p>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <x-stat-card title="Tracked Currencies" :value="$totalCurrencies" icon="bi-currency-exchange" color="primary" valueId="stat-currency-tracked" />
    </div>
    <div class="col-md-3">
        <x-stat-card title="Average Daily Change" :value="number_format((float)($avgChange ?? 0), 2) . '%'" icon="bi-graph-up-arrow" color="{{ ($avgChange ?? 0) >= 0 ? 'success' : 'danger' }}" valueId="stat-currency-avg-change" />
    </div>
    <div class="col-md-3">
        <x-stat-card title="Top Gainers" :value="$topGainers->count()" icon="bi-arrow-up-circle" color="success" valueId="stat-currency-gainers" />
    </div>
    <div class="col-md-3">
        <x-stat-card title="Top Losers" :value="$topLosers->count()" icon="bi-arrow-down-circle" color="danger" valueId="stat-currency-losers" />
    </div>
</div>

<!-- Trend Chart & Movers -->
<div class="row g-4 mb-4">
    <!-- Currency Trend Chart -->
    <div class="col-lg-8">
        <x-card title="Major Currency Trends vs USD (14-Day Snapshots)" icon="bi-graph-up">
            <canvas id="currencyTrendChart" height="300"></canvas>
        </x-card>
    </div>

    <!-- Top Movers -->
    <div class="col-lg-4">
        <x-card title="Top Gainers" icon="bi-arrow-up-circle-fill">
            <div class="d-flex flex-column gap-2" id="dashboard-gainers-list">
                @forelse($topGainers->take(5) as $g)
                <div class="d-flex justify-content-between align-items-center px-2 py-1 rounded" style="background: rgba(34,197,94,0.05);">
                    <div class="d-flex align-items-center gap-2">
                        @if($g->country)
                        <img src="{{ $g->country->flag_url }}" class="rounded" style="width:18px;height:12px;object-fit:cover;" alt="">
                        @endif
                        <span class="text-white fw-semibold small">{{ $g->currency_code }}</span>
                    </div>
                    <span class="text-success fw-bold small">+{{ number_format((float)$g->change_percent, 2) }}%</span>
                </div>
                @empty
                <span class="text-muted small">No gainers data available</span>
                @endforelse
            </div>
        </x-card>

        <div class="mt-4">
            <x-card title="Top Losers" icon="bi-arrow-down-circle-fill">
                <div class="d-flex flex-column gap-2" id="dashboard-losers-list">
                    @forelse($topLosers->take(5) as $l)
                    <div class="d-flex justify-content-between align-items-center px-2 py-1 rounded" style="background: rgba(239,68,68,0.05);">
                        <div class="d-flex align-items-center gap-2">
                            @if($l->country)
                            <img src="{{ $l->country->flag_url }}" class="rounded" style="width:18px;height:12px;object-fit:cover;" alt="">
                            @endif
                            <span class="text-white fw-semibold small">{{ $l->currency_code }}</span>
                        </div>
                        <span class="text-danger fw-bold small">{{ number_format((float)$l->change_percent, 2) }}%</span>
                    </div>
                    @empty
                    <span class="text-muted small">No losers data available</span>
                    @endforelse
                </div>
            </x-card>
        </div>
    </div>
</div>

<!-- Full Currency Table -->
<x-card title="All Exchange Rates (vs USD & IDR)" icon="bi-table">
    <div class="table-responsive">
        <x-table :headers="['Currency', 'Country', 'Rate to USD', 'Rate to IDR', 'Daily Change', 'Weekly %', 'Monthly %', 'Last Updated']" tbodyId="exchange-rates-tbody">
            @forelse($latestRates as $rate)
            <tr>
                <td><strong class="text-white">{{ $rate->currency_code }}</strong> <span class="text-muted small">{{ $rate->currency_name }}</span></td>
                <td>
                    @if($rate->country)
                    <div class="d-flex align-items-center gap-2 small">
                        <img src="{{ $rate->country->flag_url }}" class="rounded" style="width:18px;height:12px;object-fit:cover;" alt="">
                        {{ $rate->country->name }}
                    </div>
                    @else
                        <span class="text-muted small">—</span>
                    @endif
                </td>
                <td class="text-white fw-semibold">
                    @if((float)$rate->rate_to_usd > 0)
                        @if((float)$rate->rate_to_usd >= 0.01)
                            {{ number_format((float)$rate->rate_to_usd, 4) }} USD
                        @else
                            1 USD = {{ number_format(1 / (float)$rate->rate_to_usd, 2) }} {{ $rate->currency_code }}
                        @endif
                    @else
                        N/A
                    @endif
                </td>
                <td class="text-muted">{{ $rate->rate_to_idr ? number_format((float)$rate->rate_to_idr, 2) : '—' }}</td>
                <td>
                    <span class="{{ (float)($rate->change_percent ?? 0) >= 0 ? 'text-success' : 'text-danger' }} fw-semibold small">
                        {{ (float)($rate->change_percent ?? 0) >= 0 ? '+' : '' }}{{ number_format((float)($rate->change_percent ?? 0), 2) }}%
                    </span>
                </td>
                <td>
                    <span class="{{ (float)($rate->weekly_change ?? 0) >= 0 ? 'text-success' : 'text-danger' }} small">
                        {{ (float)($rate->weekly_change ?? 0) >= 0 ? '+' : '' }}{{ number_format((float)($rate->weekly_change ?? 0), 2) }}%
                    </span>
                </td>
                <td>
                    <span class="{{ (float)($rate->monthly_change ?? 0) >= 0 ? 'text-success' : 'text-danger' }} small">
                        {{ (float)($rate->monthly_change ?? 0) >= 0 ? '+' : '' }}{{ number_format((float)($rate->monthly_change ?? 0), 2) }}%
                    </span>
                </td>
                <td class="text-muted small">{{ $rate->rate_date ? \Carbon\Carbon::parse($rate->rate_date)->format('M d, Y') : '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center text-muted py-4">No exchange rate data available.</td></tr>
            @endforelse
        </x-table>
    </div>
</x-card>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const trends = @json($currencyTrends ?? []);
    const colors = [
        'rgba(59,130,246,1)', 'rgba(239,68,68,1)', 'rgba(34,197,94,1)',
        'rgba(249,115,22,1)', 'rgba(168,85,247,1)'
    ];

    if (trends.length > 0 && document.getElementById('currencyTrendChart')) {
        // Use the longest date array as labels
        let longestDates = [];
        trends.forEach(t => { if (t.dates.length > longestDates.length) longestDates = t.dates; });

        window.currencyTrendChartInstance = new Chart(document.getElementById('currencyTrendChart'), {
            type: 'line',
            data: {
                labels: longestDates,
                datasets: trends.map((t, i) => ({
                    label: t.label,
                    data: t.data,
                    borderColor: colors[i % colors.length],
                    backgroundColor: 'transparent',
                    tension: 0.3,
                    pointRadius: 2,
                    borderWidth: 2,
                }))
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { labels: { color: '#94a3b8', font: { size: 11 } } }
                },
                scales: {
                    x: { ticks: { color: '#64748b', font: { size: 10 }, maxTicksLimit: 10 }, grid: { color: 'rgba(100,116,139,0.1)' } },
                    y: { ticks: { color: '#64748b', font: { size: 10 } }, grid: { color: 'rgba(100,116,139,0.1)' } }
                }
            }
        });
    }
});
</script>
@endpush
