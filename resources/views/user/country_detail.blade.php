@extends('layouts.app')

@section('title', $countryDTO->name . ' Detail')

@section('content')
<div class="container-fluid py-4">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-decoration-none text-muted">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('countries.index') }}" class="text-decoration-none text-muted">Countries</a></li>
            <li class="breadcrumb-item active text-white" aria-current="page">{{ $countryDTO->name }}</li>
        </ol>
    </nav>

    <!-- Fallback / Live Indicator Alert -->
    @if($isOffline)
        <div class="alert alert-warning border-warning border-opacity-20 bg-warning bg-opacity-5 d-flex align-items-center gap-2 mb-4 shadow-sm" role="alert">
            <span class="spinner-grow spinner-grow-sm text-warning" role="status" aria-hidden="true"></span>
            <div>
                <strong>Offline Fallback Mode Active:</strong> API connection timeout or rate limit reached. Displaying latest cached data from database.
            </div>
        </div>
    @else
        <div class="alert alert-success border-success border-opacity-20 bg-success bg-opacity-5 d-flex align-items-center gap-2 mb-4 shadow-sm" role="alert">
            <i class="bi bi-broadcast text-success animate-ping"></i>
            <div>
                <strong>Live API Data Connection:</strong> Viewing real-time data directly from REST Countries, World Bank, Open-Meteo, and Exchange Rate APIs.
            </div>
        </div>
    @endif

    <!-- Country Header Card -->
    <div class="card card-premium border-0 mb-4 overflow-hidden">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ $countryDTO->flagUrl }}" alt="{{ $countryDTO->name }} Flag" class="rounded border border-secondary border-opacity-20 shadow-sm" style="width: 80px; height: 53px; object-fit: cover;">
                    <div>
                        <h1 class="h3 text-white mb-1 fw-bold">{{ $countryDTO->name }}</h1>
                        <p class="text-muted small mb-0">{{ $countryDTO->officialName }} &bull; {{ $countryDTO->region }} ({{ $countryDTO->subRegion }})</p>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge bg-secondary py-2 px-3 fw-bold border border-secondary border-opacity-30">ISO2: {{ $countryDTO->iso2 }}</span>
                    <span class="badge bg-secondary py-2 px-3 fw-bold border border-secondary border-opacity-30">ISO3: {{ $countryDTO->iso3 }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- LEFT COLUMN: Profile, Economic & Currency & Ports Tabs -->
        <div class="col-lg-8">
            <!-- Tabs Navigation -->
            <ul class="nav nav-tabs premium-tabs mb-4 border-0" id="countryDetailTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview-pane" type="button" role="tab" aria-controls="overview-pane" aria-selected="true">
                        <i class="bi bi-info-circle me-1"></i> Overview
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="ports-tab" data-bs-toggle="tab" data-bs-target="#ports-pane" type="button" role="tab" aria-controls="ports-pane" aria-selected="false">
                        <i class="bi bi-anchor me-1"></i> Ports & Logistics <span class="badge bg-primary bg-opacity-20 text-primary ms-1" style="font-size: 0.72rem; vertical-align: middle;">{{ $countryModel->ports->count() }}</span>
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="countryDetailTabsContent">
                <!-- OVERVIEW TAB -->
                <div class="tab-pane fade show active" id="overview-pane" role="tabpanel" aria-labelledby="overview-tab">
                    <!-- Profile Card -->
                    <div class="card card-premium border-0 mb-4">
                        <div class="card-header bg-transparent border-bottom py-3" style="border-color: var(--color-border) !important;">
                            <h5 class="card-title text-white mb-0 fs-6 fw-semibold"><i class="bi bi-info-circle me-2 text-primary"></i>Country Profile & Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <span class="text-muted small d-block">Capital City</span>
                                    <span class="text-white fw-medium">{{ $countryDTO->capital ?? 'N/A' }}</span>
                                </div>
                                <div class="col-sm-6">
                                    <span class="text-muted small d-block">Total Population</span>
                                    <span class="text-white fw-medium">{{ number_format($countryDTO->population) }}</span>
                                </div>
                                <div class="col-sm-6">
                                    <span class="text-muted small d-block">Total Area Size</span>
                                    <span class="text-white fw-medium">{{ $countryDTO->area ? number_format($countryDTO->area) . ' sq km' : 'N/A' }}</span>
                                </div>
                                <div class="col-sm-6">
                                    <span class="text-muted small d-block">Official TLD Domain</span>
                                    <span class="text-white fw-medium"><code>{{ $countryDTO->tld ?? 'N/A' }}</code></span>
                                </div>
                                <div class="col-sm-6">
                                    <span class="text-muted small d-block">Composite Risk Score</span>
                                    @if($countryModel->latestRiskScore)
                                        @php
                                            $level = $countryModel->latestRiskScore->risk_level;
                                            $badgeType = 'success';
                                            if ($level === 'high' || $level === 'critical') $badgeType = 'danger';
                                            elseif ($level === 'medium') $badgeType = 'warning';
                                        @endphp
                                        <x-badge type="{{ $badgeType }}">
                                            {{ number_format($countryModel->latestRiskScore->composite_score, 2) }} ({{ ucfirst($level) }})
                                        </x-badge>
                                    @else
                                        <span class="text-white fw-medium">N/A</span>
                                    @endif
                                </div>
                                <div class="col-sm-6">
                                    <span class="text-muted small d-block">Risk Last Calculated</span>
                                    <span class="text-white fw-medium" id="risk-last-calculated">
                                        {{ $countryModel->latestRiskScore ? $countryModel->latestRiskScore->calculated_at->diffForHumans() : 'Never' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Risk Breakdown Card -->
                    @if($countryModel->latestRiskScore)
                    @php
                        $scoreDetails = $countryModel->latestRiskScore->details->keyBy('riskCategory.slug');
                    @endphp
                    <div class="card card-premium border-0 mb-4" id="country-detail-risk-breakdown-card" data-country-code="{{ $countryModel->iso2 }}">
                        <div class="card-header bg-transparent border-bottom py-3" style="border-color: var(--color-border) !important;">
                            <h5 class="card-title text-white mb-0 fs-6 fw-semibold">
                                <i class="bi bi-shield-exclamation me-2 text-primary"></i>Risk Category Breakdown
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-column gap-3">
                                @foreach([
                                    'economic-risk' => ['label' => 'Economic Risk', 'color' => 'bg-danger'],
                                    'weather-risk' => ['label' => 'Weather Risk', 'color' => 'bg-warning text-dark'],
                                    'currency-stability-risk' => ['label' => 'Currency Stability Risk', 'color' => 'bg-success'],
                                    'geopolitical-risk' => ['label' => 'Geopolitical Risk', 'color' => 'bg-primary'],
                                    'logistics-risk' => ['label' => 'Logistics Risk', 'color' => 'bg-info text-dark']
                                ] as $slug => $meta)
                                @php
                                    $detail = $scoreDetails->get($slug);
                                    $val = $detail ? (float)$detail->category_score : 0.0;
                                    $weighted = $detail ? (float)$detail->weighted_score : 0.0;
                                @endphp
                                <div id="risk-category-row-{{ $slug }}">
                                    <div class="d-flex justify-content-between small text-muted mb-1">
                                        <span class="text-white fw-semibold">{{ $meta['label'] }}</span>
                                        <span>
                                            Score: <strong class="score-val">{{ number_format($val, 2) }}</strong> 
                                            (Weighted: <strong class="score-weighted">{{ number_format($weighted, 2) }}</strong>)
                                        </span>
                                    </div>
                                    <div class="progress" style="height: 10px; background-color: var(--color-border);">
                                        <div class="progress-bar {{ $meta['color'] }}" role="progressbar" style="width: {{ $val }}%" aria-valuenow="{{ $val }}" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                                <div class="pt-2 border-top border-secondary border-opacity-10 d-flex justify-content-between align-items-center">
                                    <span class="text-white fw-bold">Total Composite Risk Rating</span>
                                    <span class="fs-5 text-white fw-extrabold" id="total-composite-risk-value">{{ number_format($countryModel->latestRiskScore->composite_score, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Economic Indicators Card -->
                    <div class="card card-premium border-0 mb-4">
                        <div class="card-header bg-transparent border-bottom py-3" style="border-color: var(--color-border) !important;">
                            <h5 class="card-title text-white mb-0 fs-6 fw-semibold"><i class="bi bi-graph-up me-2 text-primary"></i>Economic Indicators (World Bank Live)</h5>
                        </div>
                        <div class="card-body p-0">
                            <x-table :headers="['Indicator Name', 'Year', 'Value', 'Unit', 'Source']">
                                @forelse($indicators as $ind)
                                <tr>
                                    <td><strong>{{ $ind->indicator_name }}</strong></td>
                                    <td>{{ $ind->year }}</td>
                                    <td>{{ number_format($ind->value, 2) }}</td>
                                    <td class="small text-muted">{{ $ind->unit ?? 'USD' }}</td>
                                    <td><x-badge type="success">{{ $ind->source }}</x-badge></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">No economic indicators available for this country.</td>
                                </tr>
                                @endforelse
                            </x-table>
                        </div>
                    </div>

                    <!-- Currency & Exchange Rate -->
                    <div class="card card-premium border-0 mb-4">
                        <div class="card-header bg-transparent border-bottom py-3" style="border-color: var(--color-border) !important;">
                            <h5 class="card-title text-white mb-0 fs-6 fw-semibold"><i class="bi bi-currency-exchange me-2 text-primary"></i>Currency Exchange Rates</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3 align-items-center">
                                <div class="col-sm-6">
                                    <span class="text-muted small d-block">Currency Unit</span>
                                    <h4 class="text-white fw-bold mb-1">{{ $countryDTO->currencyName }} ({{ $countryDTO->currencySymbol }})</h4>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 px-2.5 py-1 small fw-bold">Code: {{ $countryDTO->currencyCode }}</span>
                                </div>
                                <div class="col-sm-6">
                                    <div class="p-3 rounded border border-secondary border-opacity-20" style="background-color: rgba(255,255,255,0.01);">
                                        <span class="text-muted small d-block">Relative Exchange Value</span>
                                        <div class="d-flex flex-column gap-1 mt-2">
                                            <div class="d-flex justify-content-between">
                                                <span class="text-muted small">1 {{ $countryDTO->currencyCode }} =</span>
                                                <span class="text-white fw-semibold">{{ $exchangeRate ? round($exchangeRate->rate_to_usd, 6) . ' USD' : 'N/A' }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span class="text-muted small">1 {{ $countryDTO->currencyCode }} =</span>
                                                <span class="text-white fw-semibold">{{ $exchangeRate ? number_format($exchangeRate->rate_to_idr, 2) . ' IDR' : 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PORTS TAB -->
                <div class="tab-pane fade" id="ports-pane" role="tabpanel" aria-labelledby="ports-tab">
                    <div class="card card-premium border-0 mb-4">
                        <div class="card-header bg-transparent border-bottom py-3" style="border-color: var(--color-border) !important;">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title text-white mb-0 fs-6 fw-semibold">
                                    <i class="bi bi-anchor me-2 text-primary"></i>Shipping Ports & Logistics Directory
                                </h5>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 px-2.5 py-1 small fw-bold">
                                    Total Ports: {{ $countryModel->ports->count() }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <x-table :headers="['Port Name', 'Code', 'Coordinates', 'Harbor Type', 'Shelter', 'Max Depth']">
                                @forelse($countryModel->ports as $port)
                                <tr>
                                    <td class="align-middle"><strong>{{ $port->name }}</strong></td>
                                    <td class="align-middle small"><code>{{ $port->port_code ?? 'N/A' }}</code></td>
                                    <td class="align-middle small text-muted">
                                        {{ number_format($port->latitude, 4) }}, {{ number_format($port->longitude, 4) }}
                                    </td>
                                    <td class="align-middle small text-capitalize">{{ $port->harbor_type ?? 'N/A' }}</td>
                                    <td class="align-middle small text-capitalize">{{ $port->shelter ?? 'N/A' }}</td>
                                    <td class="align-middle small text-white">{{ $port->max_depth ? $port->max_depth . ' m' : 'N/A' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No ports registered for this country.</td>
                                </tr>
                                @endforelse
                            </x-table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: Weather & News -->
        <div class="col-lg-4">
            <!-- Weather Card -->
            <div class="card card-premium border-0 mb-4">
                <div class="card-header bg-transparent border-bottom py-3" style="border-color: var(--color-border) !important;">
                    <h5 class="card-title text-white mb-0 fs-6 fw-semibold"><i class="bi bi-cloud-sun me-2 text-primary"></i>Local Weather (Open-Meteo Live)</h5>
                </div>
                <div class="card-body text-center py-4">
                    @if($weather)
                        <div class="display-3 text-warning mb-2">
                            <i class="bi bi-thermometer-half"></i>
                        </div>
                        <h2 class="text-white fw-bold mb-1">{{ number_format($weather->temperature, 1) }}&deg;C</h2>
                        <p class="text-muted small mb-3">Apparent Temperature: {{ number_format($weather->feels_like, 1) }}&deg;C</p>
                        
                        <div class="d-flex justify-content-center gap-2 mb-3">
                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-20 py-1.5 px-2.5 fw-semibold fs-7">{{ $weather->weather_description }}</span>
                        </div>

                        <div class="row g-2 text-start pt-3 border-top border-secondary border-opacity-10">
                            <div class="col-6">
                                <span class="text-muted small d-block">Wind Speed</span>
                                <span class="text-white fw-semibold small">{{ $weather->wind_speed }} km/h</span>
                            </div>
                            <div class="col-6">
                                <span class="text-muted small d-block">Relative Humidity</span>
                                <span class="text-white fw-semibold small">{{ $weather->humidity }}%</span>
                            </div>
                            <div class="col-6">
                                <span class="text-muted small d-block">Surface Pressure</span>
                                <span class="text-white fw-semibold small">{{ $weather->pressure }} hPa</span>
                            </div>
                            <div class="col-6">
                                <span class="text-muted small d-block">Visibility</span>
                                <span class="text-white fw-semibold small">{{ $weather->visibility / 1000 }} km</span>
                            </div>
                        </div>
                    @else
                        <div class="text-muted py-4">
                            <i class="bi bi-cloud-slash display-5 mb-2"></i>
                            <p class="small mb-0">No live weather data available.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- News Feed Card -->
            <div class="card card-premium border-0">
                <div class="card-header bg-transparent border-bottom py-3" style="border-color: var(--color-border) !important;">
                    <h5 class="card-title text-white mb-0 fs-6 fw-semibold"><i class="bi bi-newspaper me-2 text-primary"></i>Geopolitical & Economic News</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-3">
                        @forelse($news as $article)
                        @php
                            $sentimentColor = 'bg-secondary';
                            if ($article->sentiment === 'positive') $sentimentColor = 'bg-success';
                            elseif ($article->sentiment === 'negative') $sentimentColor = 'bg-danger';
                        @endphp
                        <div class="p-2.5 rounded border border-secondary border-opacity-10" style="background-color: rgba(255, 255, 255, 0.01);">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge {{ $sentimentColor }} bg-opacity-10 text-{{ $article->sentiment }} border border-{{ $article->sentiment }} border-opacity-20 small fw-bold">{{ strtoupper($article->sentiment) }}</span>
                                <span class="text-muted small" style="font-size: 0.7rem;">{{ $article->published_at->diffForHumans() }}</span>
                            </div>
                            <h6 class="text-white mb-1 fw-semibold small"><a href="{{ $article->source_url }}" target="_blank" class="text-decoration-none text-white hover-primary">{{ $article->title }}</a></h6>
                            <span class="text-muted small d-block" style="font-size: 0.72rem;">Source: {{ $article->source_name }}</span>
                        </div>
                        @empty
                        <div class="text-center py-4 px-3 rounded border border-warning border-opacity-10 bg-warning bg-opacity-5">
                            <i class="bi bi-exclamation-triangle-fill text-warning display-7 mb-2 d-block"></i>
                            <h6 class="text-white mb-1 small fw-semibold">No Live News Feed Available</h6>
                            <p class="text-muted small mb-0" style="font-size: 0.75rem; line-height: 1.3;">
                                <strong>Cause:</strong> GNews API Key is missing or GNews API daily limit of 100 free requests has been reached. Showing offline cached repository news instead.
                            </p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes ping {
    75%, 100% {
        transform: scale(2);
        opacity: 0;
    }
}
.animate-ping {
    animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;
}
.hover-primary:hover {
    color: var(--bs-primary) !important;
}
/* Premium Tabs Style overrides */
.premium-tabs .nav-link {
    color: var(--color-text-muted, #adb5bd) !important;
    background-color: transparent !important;
    border: none !important;
    border-bottom: 2px solid transparent !important;
    font-weight: 600 !important;
    font-size: 0.95rem;
    padding: 8px 16px;
    border-radius: 0 !important;
    transition: all 0.2s ease-in-out;
}
.premium-tabs .nav-link:hover {
    color: var(--color-text-main, #ffffff) !important;
    border-bottom-color: rgba(255, 255, 255, 0.2) !important;
}
.premium-tabs .nav-link.active {
    color: var(--bs-primary, #0d6efd) !important;
    border-bottom: 2px solid var(--bs-primary, #0d6efd) !important;
}
</style>
@endsection
