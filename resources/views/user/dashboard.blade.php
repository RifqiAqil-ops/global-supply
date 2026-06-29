@extends('layouts.app')

@section('title', 'Risk Intelligence Dashboard')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 text-white mb-1">Global Risk Overview</h1>
        <p class="text-muted small mb-0">Consolidated risk score across global logistics, weather, and macro economics.</p>
    </div>
    <div>
        <form method="POST" action="{{ route('user.refresh-metrics') }}" class="m-0">
            @csrf
            <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="bi bi-arrow-clockwise"></i> Refresh Metrics
            </button>
        </form>
    </div>
</div>

<!-- Summary Cards Section -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <x-stat-card 
            title="Global Average Risk Index" 
            value="43.2" 
            change="+1.4%" 
            changeType="up" 
            icon="bi-shield-exclamation" 
            iconColor="warning" 
        />
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-stat-card 
            title="Countries Monitored" 
            value="{{ $countriesMonitored }}" 
            change="All Clear" 
            changeType="neutral" 
            icon="bi-globe" 
            iconColor="primary" 
        />
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-stat-card 
            title="Active Extreme Events" 
            value="{{ $extremeWeatherCount }}" 
            change="+2 events" 
            changeType="up" 
            icon="bi-cloud-lightning-rain" 
            iconColor="danger" 
        />
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-stat-card 
            title="Currency Pairs Monitored" 
            value="{{ $currenciesCount }}" 
            change="-0.4% Volatility" 
            changeType="down" 
            icon="bi-currency-exchange" 
            iconColor="success" 
        />
    </div>
</div>

<!-- Main Row: Interactive Map & Risk Ranks -->
<div class="row g-4 mb-4">
    <!-- Map Placeholder Section -->
    <div class="col-lg-8">
        <div class="card card-premium border-0 h-100">
            <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between py-3" style="border-color: var(--color-border) !important;">
                <h5 class="card-title text-white mb-0 fs-6 fw-semibold">
                    <i class="bi bi-map-fill me-2 text-primary"></i>Interactive Risk Map
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 small">Low Risk</span>
                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-20 small">Medium</span>
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20 small">High</span>
                </div>
            </div>
            <div class="card-body p-0 d-flex flex-column align-items-center justify-content-center" style="min-height: 400px; background-color: rgba(0,0,0,0.2);">
                <!-- Dynamic Interactive Map Area Placeholder -->
                <div class="text-center py-5">
                    <div class="display-6 text-muted mb-3"><i class="bi bi-geo-alt"></i></div>
                    <h5 class="text-white mb-2">Leaflet Interactive Map Area</h5>
                    <p class="text-muted small mx-auto" style="max-width: 320px;">Geographical representation of country-level composite risk index will render here in Phase 6.</p>
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Loading map...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Risk Countries List -->
    <div class="col-lg-4">
        <x-card title="Top Risk Hotspots" icon="bi-exclamation-triangle">
            <x-table :headers="['Country', 'Risk Index', 'Trend']">
                <tr>
                    <td>
                        <span class="d-flex align-items-center gap-2 small">
                            <span class="fs-6">🇻🇪</span> Venezuela
                        </span>
                    </td>
                    <td><x-badge type="danger">78.5 (Critical)</x-badge></td>
                    <td><span class="text-danger small"><i class="bi bi-arrow-up"></i> High</span></td>
                </tr>
                <tr>
                    <td>
                        <span class="d-flex align-items-center gap-2 small">
                            <span class="fs-6">🇮🇷</span> Iran
                        </span>
                    </td>
                    <td><x-badge type="danger">72.1 (High)</x-badge></td>
                    <td><span class="text-danger small"><i class="bi bi-arrow-up"></i> High</span></td>
                </tr>
                <tr>
                    <td>
                        <span class="d-flex align-items-center gap-2 small">
                            <span class="fs-6">🇹🇷</span> Turkey
                        </span>
                    </td>
                    <td><x-badge type="warning">64.8 (Medium)</x-badge></td>
                    <td><span class="text-success small"><i class="bi bi-arrow-down"></i> Lower</span></td>
                </tr>
                <tr>
                    <td>
                        <span class="d-flex align-items-center gap-2 small">
                            <span class="fs-6">🇦🇷</span> Argentina
                        </span>
                    </td>
                    <td><x-badge type="warning">59.0 (Medium)</x-badge></td>
                    <td><span class="text-muted small"><i class="bi bi-dash"></i> Stable</span></td>
                </tr>
                <tr>
                    <td>
                        <span class="d-flex align-items-center gap-2 small">
                            <span class="fs-6">🇬🇧</span> United Kingdom
                        </span>
                    </td>
                    <td><x-badge type="success">18.2 (Low)</x-badge></td>
                    <td><span class="text-success small"><i class="bi bi-arrow-down"></i> Lower</span></td>
                </tr>
            </x-table>
        </x-card>
    </div>
</div>

<!-- Secondary Row: Watchlist Table & Chart -->
<div class="row g-4 mb-4">
    <!-- Watchlist Table Placeholder -->
    <div class="col-lg-7">
        <x-card title="My Sourcing Watchlist" icon="bi-eye">
            <x-slot name="headerActions">
                <a href="{{ route('watchlists.index') }}" class="btn btn-sm btn-secondary py-1 px-3">View All</a>
            </x-slot>
            
            <x-table :headers="['Country Code', 'Sourcing Region', 'Risk Score', 'Port Status', 'Action']">
                @forelse($watchlistCountries as $c)
                @php
                    $weather = $c->weatherData->first();
                    $weatherDesc = $weather ? $weather->weather_description : 'No Data';
                    $portStatus = 'Active';
                    $portIcon = 'bi-check-circle-fill';
                    $portColor = 'text-success';
                    
                    if ($c->iso2 === 'CN') {
                        $portStatus = 'Congested';
                        $portIcon = 'bi-exclamation-triangle-fill';
                        $portColor = 'text-warning';
                    } elseif ($c->iso2 === 'PH') {
                        $portStatus = 'Disrupted';
                        $portIcon = 'bi-x-circle-fill';
                        $portColor = 'text-danger';
                    }
                @endphp
                <tr>
                    <td><strong>{{ $c->iso3 }}</strong></td>
                    <td class="small text-muted">{{ $c->region }}</td>
                    <td>
                        <x-badge type="{{ $c->iso2 === 'PH' ? 'danger' : ($c->iso2 === 'US' ? 'success' : 'warning') }}">
                            {{ $c->iso2 === 'PH' ? '62.4 (High)' : ($c->iso2 === 'US' ? '12.5 (Low)' : '39.8 (Medium)') }}
                        </x-badge>
                    </td>
                    <td><span class="{{ $portColor }}"><i class="bi {{ $portIcon }} me-1"></i> {{ $portStatus }}</span></td>
                    <td><a href="{{ route('countries.show', $c->iso2) }}" class="btn btn-sm btn-link text-primary p-0 text-decoration-none">Details</a></td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-3">No watchlist countries configured.</td>
                </tr>
                @endforelse
            </x-table>
        </x-card>
    </div>

    <!-- Chart Placeholder -->
    <div class="col-lg-5">
        <div class="card card-premium border-0 h-100">
            <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between py-3" style="border-color: var(--color-border) !important;">
                <h5 class="card-title text-white mb-0 fs-6 fw-semibold">
                    <i class="bi bi-graph-up-arrow me-2 text-primary"></i>Historical Risk Score Trend
                </h5>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center" style="min-height: 250px;">
                <div class="text-center py-4">
                    <div class="display-6 text-muted mb-2"><i class="bi bi-bar-chart-line"></i></div>
                    <h6 class="text-white mb-1">Chart.js Visual Timeline</h6>
                    <p class="text-muted small mx-auto" style="max-width: 280px;">Trendline charts and category radar scopes will render here in Phase 6.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bottom Row: Recent News & System Activity Feed -->
<div class="row g-4">
    <!-- Geopolitical & Economics News Feed -->
    <div class="col-lg-6">
        <x-card title="Supply Chain Intelligence News" icon="bi-newspaper">
            <div class="d-flex flex-column gap-3">
                <div class="p-2.5 rounded border border-secondary border-opacity-10 d-flex gap-3 align-items-start" style="background-color: rgba(255, 255, 255, 0.01);">
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20 mt-1 small">Geopolitics</span>
                    <div>
                        <h6 class="text-white mb-1 fw-semibold small">Suez Canal Route Tension Escalates With Local Shipping Restrictions</h6>
                        <span class="text-muted small d-block" style="font-size: 0.75rem;">Source: Lloyd\'s List &bull; 10 mins ago</span>
                    </div>
                </div>
                <div class="p-2.5 rounded border border-secondary border-opacity-10 d-flex gap-3 align-items-start" style="background-color: rgba(255, 255, 255, 0.01);">
                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-20 mt-1 small">Logistics</span>
                    <div>
                        <h6 class="text-white mb-1 fw-semibold small">Manila Terminal Congestion Rises by 15% due to Monsoon Weather Fronts</h6>
                        <span class="text-muted small d-block" style="font-size: 0.75rem;">Source: PortNews &bull; 45 mins ago</span>
                    </div>
                </div>
                <div class="p-2.5 rounded border border-secondary border-opacity-10 d-flex gap-3 align-items-start" style="background-color: rgba(255, 255, 255, 0.01);">
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 mt-1 small">Economics</span>
                    <div>
                        <h6 class="text-white mb-1 fw-semibold small">World Bank Lowers Regional GDP Growth Forecast for Latin America</h6>
                        <span class="text-muted small d-block" style="font-size: 0.75rem;">Source: Reuters &bull; 2 hours ago</span>
                    </div>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Activity Log / User Feed -->
    <div class="col-lg-6">
        <x-card title="System Operations Activity Feed" icon="bi-activity">
            <div class="d-flex flex-column gap-3">
                <div class="d-flex gap-2">
                    <div class="text-success"><i class="bi bi-plus-circle-fill"></i></div>
                    <div>
                        <span class="text-white small fw-semibold">Watchlist Added</span>
                        <p class="text-muted small mb-0" style="font-size: 0.75rem;">User logged country item \'PHL\' to primary watchlist.</p>
                        <span class="text-muted small" style="font-size: 0.7rem;">Today, 10:14 AM</span>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <div class="text-primary"><i class="bi bi-info-circle-fill"></i></div>
                    <div>
                        <span class="text-white small fw-semibold">Task Scheduler Completed</span>
                        <p class="text-muted small mb-0" style="font-size: 0.75rem;">Scheduled hourly job fetched current Open-Meteo values for 195 capitals.</p>
                        <span class="text-muted small" style="font-size: 0.7rem;">Today, 10:00 AM</span>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <div class="text-warning"><i class="bi bi-exclamation-triangle-fill"></i></div>
                    <div>
                        <span class="text-white small fw-semibold">Scoring Weights Adjusted</span>
                        <p class="text-muted small mb-0" style="font-size: 0.75rem;">Admin account adjusted Geopolitical Risk index weight from 0.20 to 0.25.</p>
                        <span class="text-muted small" style="font-size: 0.7rem;">Yesterday, 4:30 PM</span>
                    </div>
                </div>
            </div>
        </x-card>
    </div>
</div>
@endsection
