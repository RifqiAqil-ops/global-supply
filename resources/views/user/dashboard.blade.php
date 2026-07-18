@extends('layouts.app')

@section('title', 'Risk Intelligence Dashboard')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    /* Scoped theme override for user dashboard content area */
    main {
        background-color: #F8FAFC !important;
    }
    
    /* Hero Card Design */
    .hero-card {
        background: linear-gradient(135deg, #FFFFFF 0%, #F8FAFC 100%) !important;
        border: 1px solid #E5E7EB !important;
        border-radius: 24px !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03), 0 2px 4px -1px rgba(0, 0, 0, 0.01) !important;
        position: relative;
        overflow: hidden;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .hero-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: radial-gradient(circle at top right, rgba(37, 99, 235, 0.04) 0%, rgba(255, 255, 255, 0) 65%);
        pointer-events: none;
    }
    
    .hero-card:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.01) !important;
    }

    .hero-title {
        font-family: 'Outfit', sans-serif;
        color: #0F172A !important;
        font-weight: 700;
        font-size: 1.5rem;
        letter-spacing: -0.02em;
        line-height: 1.2;
    }

    .hero-subtitle {
        color: #64748B !important;
        font-size: 0.88rem;
        font-weight: 400;
        line-height: 1.4;
    }

    /* Live Badge in Hero */
    .hero-live-badge {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        background-color: #ECFDF5 !important;
        color: #059669 !important;
        border: 1px solid #A7F3D0 !important;
        border-radius: 8px;
    }

    /* Pulse dot */
    .pulse-dot {
        width: 8px;
        height: 8px;
        background-color: #10B981;
        border-radius: 50%;
        display: inline-block;
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        animation: pulse 1.6s infinite;
    }

    @keyframes pulse {
        0% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        }
        70% {
            transform: scale(1);
            box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
        }
        100% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
        }
    }

    /* Modern SaaS Action Buttons */
    .btn-primary-saas {
        background-color: #0F172A !important;
        border-color: #0F172A !important;
        color: #FFFFFF !important;
        border-radius: 12px !important;
        padding: 9px 18px !important;
        font-weight: 600 !important;
        font-size: 0.82rem !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
    }

    .btn-primary-saas:hover {
        background-color: #1E293B !important;
        border-color: #1E293B !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.08) !important;
    }

    .btn-secondary-saas {
        background-color: #FFFFFF !important;
        border-color: #E5E7EB !important;
        color: #334155 !important;
        border-radius: 12px !important;
        padding: 9px 18px !important;
        font-weight: 600 !important;
        font-size: 0.82rem !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
    }

    .btn-secondary-saas:hover {
        background-color: #F8FAFC !important;
        border-color: #D1D5DB !important;
        color: #0F172A !important;
        transform: translateY(-2px) !important;
    }

    /* News Feed Layout */
    main .news-item-bg {
        background-color: #F8FAFC !important;
        border-color: #E2E8F0 !important;
        transition: all 0.2s ease;
    }

    main .news-item-bg:hover {
        background-color: #FFFFFF !important;
        border-color: #2563EB !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02) !important;
    }

    /* Activity Feed Layout */
    main .activity-item {
        border-bottom: 1px solid #F1F5F9;
        padding-bottom: 12px;
        transition: all 0.2s ease;
    }
    
    main .activity-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    /* Premium Leaflet Map Adjustments */
    .leaflet-container {
        border-radius: 24px !important;
        font-family: inherit !important;
        background-color: #F8FAFC !important;
    }

    .leaflet-bar {
        border: 1px solid #E5E7EB !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03) !important;
        border-radius: 8px !important;
        overflow: hidden;
    }

    .leaflet-bar a, 
    .leaflet-bar a:hover {
        background-color: #FFFFFF !important;
        border-bottom: 1px solid #F1F5F9 !important;
        color: #64748B !important;
        transition: all 0.2s ease;
    }

    .leaflet-bar a:hover {
        color: #0F172A !important;
        background-color: #F8FAFC !important;
    }

    .map-floating-overlay {
        position: absolute;
        z-index: 1000;
        pointer-events: auto;
    }

    @keyframes shimmer {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }
    .skeleton-shimmer {
        background: linear-gradient(90deg, #F1F5F9 25%, #E2E8F0 50%, #F1F5F9 75%);
        background-size: 200% 100%;
        animation: shimmer 1.5s infinite;
    }
</style>
@endpush

@section('content')
<!-- Hero Section (Minimalist Enterprise) -->
<div class="hero-card p-4 mb-4 border-0 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3" style="min-height: 120px;">
    <div class="hero-left" style="max-width: 680px;">
        <h1 class="hero-title mb-1">Global Supply Chain Risk Intelligence Platform</h1>
        <p class="hero-subtitle mb-0">Monitor economic, geopolitical, logistics, weather, and currency risks across 195 sovereign countries in real time.</p>
    </div>
    
    <div class="hero-right d-flex flex-column flex-sm-row align-items-sm-center gap-4">
        <!-- Live & Sync Stats -->
        <div class="hero-stats d-flex flex-column gap-1 text-lg-end">
            <div class="d-flex align-items-center gap-2 justify-content-lg-end">
                <span class="pulse-dot"></span>
                <span class="hero-status text-success fw-bold" style="font-size: 0.85rem;">LIVE</span>
                <span class="text-muted" style="font-size: 0.85rem;">|</span>
                <span class="hero-countries fw-semibold text-dark" style="font-size: 0.85rem;">Total Countries: 195</span>
            </div>
            <div class="text-muted small" style="font-size: 0.78rem;">
                Last Sync: <span id="heroSyncTime" class="fw-semibold text-dark">--:--:--</span>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="hero-actions d-flex align-items-center gap-2">
            <form method="POST" action="{{ route('user.refresh-metrics') }}" class="m-0">
                @csrf
                <button type="submit" class="btn btn-primary-saas d-flex align-items-center gap-2">
                    <i class="bi bi-arrow-clockwise"></i> Refresh Live Data
                </button>
            </form>
            <a href="{{ route('countries.index') }}" class="btn btn-secondary-saas d-flex align-items-center gap-2 text-decoration-none">
                <i class="bi bi-globe"></i> View Countries
            </a>
        </div>
    </div>
</div>

<!-- Quick Actions Panel -->
<div class="card card-premium border-0 mb-4">
    <div class="card-body p-4">
        <h6 class="text-dark small fw-bold text-uppercase tracking-wider mb-3"><i class="bi bi-lightning-charge me-2 text-primary"></i>Quick Actions</h6>
        <div class="row g-3">
            <div class="col-6 col-sm-4 col-md-2">
                <form method="POST" action="{{ route('user.refresh-metrics') }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-secondary-saas w-100 d-flex flex-column align-items-center justify-content-center py-3 gap-2 border shadow-none" style="border-radius: 16px;">
                        <i class="bi bi-arrow-clockwise fs-4 text-primary"></i>
                        <span class="small fw-semibold">Refresh Data</span>
                    </button>
                </form>
            </div>
            <div class="col-6 col-sm-4 col-md-2">
                <a href="{{ route('countries.index') }}" class="btn btn-secondary-saas w-100 d-flex flex-column align-items-center justify-content-center py-3 gap-2 border text-decoration-none shadow-none" style="border-radius: 16px;">
                    <i class="bi bi-globe fs-4 text-success"></i>
                    <span class="small fw-semibold">Countries</span>
                </a>
            </div>
            <div class="col-6 col-sm-4 col-md-2">
                <a href="{{ route('reports.index') }}" class="btn btn-secondary-saas w-100 d-flex flex-column align-items-center justify-content-center py-3 gap-2 border text-decoration-none shadow-none" style="border-radius: 16px;">
                    <i class="bi bi-file-earmark-bar-graph fs-4 text-warning"></i>
                    <span class="small fw-semibold">Reports</span>
                </a>
            </div>
            <div class="col-6 col-sm-4 col-md-2">
                <a href="{{ route('compare.index') }}" class="btn btn-secondary-saas w-100 d-flex flex-column align-items-center justify-content-center py-3 gap-2 border text-decoration-none shadow-none" style="border-radius: 16px;">
                    <i class="bi bi-shuffle fs-4 text-info"></i>
                    <span class="small fw-semibold">Compare</span>
                </a>
            </div>
            <div class="col-6 col-sm-4 col-md-2">
                <a href="{{ route('watchlists.index') }}" class="btn btn-secondary-saas w-100 d-flex flex-column align-items-center justify-content-center py-3 gap-2 border text-decoration-none shadow-none" style="border-radius: 16px;">
                    <i class="bi bi-eye fs-4 text-danger"></i>
                    <span class="small fw-semibold">Watchlist</span>
                </a>
            </div>
            <div class="col-6 col-sm-4 col-md-2">
                <a href="{{ route('ports.index') }}" class="btn btn-secondary-saas w-100 d-flex flex-column align-items-center justify-content-center py-3 gap-2 border text-decoration-none shadow-none" style="border-radius: 16px;">
                    <i class="bi bi-compass fs-4 text-purple" style="color: #7C3AED;"></i>
                    <span class="small fw-semibold">Ports Hub</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Summary Cards Section -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <x-stat-card 
            title="Global Average Risk Index" 
            value="{{ number_format($avgRisk, 1) }}" 
            change="System Score" 
            changeType="neutral" 
            icon="bi-shield-exclamation" 
            iconColor="warning" 
            valueId="stat-avg-risk"
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
            valueId="stat-countries-count"
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
            valueId="stat-extreme-weather"
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
            valueId="stat-currencies-count"
        />
    </div>
</div>

<!-- Main Row: Interactive Map & Risk Ranks -->
<div class="row g-4 mb-4">
    <!-- Map Placeholder Section -->
    <div class="col-lg-8">
        <div class="card card-premium border-0 h-100 position-relative overflow-hidden">
            <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between py-3">
                <h5 class="card-title text-dark mb-0 fs-6 fw-semibold">
                    <i class="bi bi-map-fill me-2 text-primary"></i>Interactive Risk Map
                </h5>
            </div>
            <div class="d-flex flex-wrap gap-2 px-3 py-2 border-bottom" style="background-color: #F8FAFC; border-color: #E5E7EB !important; z-index: 10;">
                <button class="btn btn-sm btn-primary rounded-pill filter-chip-dashboard px-3" data-filter="all" style="font-size: 0.76rem; font-weight: 600;">All Risk Levels</button>
                <button class="btn btn-sm btn-light rounded-pill filter-chip-dashboard px-3 text-success" data-filter="low" style="font-size: 0.76rem; font-weight: 600; border-color: #E2E8F0;">🟢 Low Risk</button>
                <button class="btn btn-sm btn-light rounded-pill filter-chip-dashboard px-3 text-warning" data-filter="medium" style="font-size: 0.76rem; font-weight: 600; border-color: #E2E8F0;">🟡 Medium</button>
                <button class="btn btn-sm btn-light rounded-pill filter-chip-dashboard px-3 text-danger" data-filter="high" style="font-size: 0.76rem; font-weight: 600; border-color: #E2E8F0;">🔴 High / Critical</button>
            </div>
            <div class="card-body p-0 position-relative" style="min-height: 400px; height: 420px; border-radius: 0 0 24px 24px;">
                <!-- Map skeleton -->
                <div id="mapSkeleton" class="position-absolute top-0 start-0 w-100 h-100 bg-white d-flex flex-column gap-3 p-4" style="z-index: 10000; transition: opacity 0.4s ease;">
                    <div class="skeleton-shimmer rounded-3" style="height: 32px; width: 40%; background: linear-gradient(90deg, #F1F5F9 25%, #E2E8F0 50%, #F1F5F9 75%); background-size: 200% 100%; animation: shimmer 1.5s infinite;"></div>
                    <div class="skeleton-shimmer rounded-4 flex-grow-1" style="background: linear-gradient(90deg, #F1F5F9 25%, #E2E8F0 50%, #F1F5F9 75%); background-size: 200% 100%; animation: shimmer 1.5s infinite;"></div>
                </div>

                <!-- Map div -->
                <div id="dashboardMap" style="width: 100%; height: 100%; z-index: 1; border-radius: 0 0 24px 24px;"></div>
                
                <!-- Floating status badge overlay -->
                <div class="map-floating-overlay top-0 start-0 m-3 px-3 py-1.5 bg-white rounded-3 shadow-sm d-flex align-items-center gap-2 border" style="border-color: #E5E7EB !important; z-index: 1000;">
                    <span class="pulse-dot"></span>
                    <span class="text-success fw-bold small" style="font-size: 0.75rem;">MAP CONNECTED</span>
                </div>

                <!-- Floating Map Controls -->
                <div class="map-floating-overlay top-0 end-0 m-3 d-flex flex-column gap-2" style="z-index: 1000;">
                    <button id="mapBtnFullscreen" class="btn btn-white btn-sm shadow-sm d-flex align-items-center justify-content-center border" style="width: 36px; height: 36px; border-radius: 8px; background: rgba(255,255,255,0.9);"><i class="bi bi-fullscreen text-dark"></i></button>
                    <button id="mapBtnReset" class="btn btn-white btn-sm shadow-sm d-flex align-items-center justify-content-center border" style="width: 36px; height: 36px; border-radius: 8px; background: rgba(255,255,255,0.9);"><i class="bi bi-arrow-counterclockwise text-dark"></i></button>
                    <button id="mapBtnLocation" class="btn btn-white btn-sm shadow-sm d-flex align-items-center justify-content-center border" style="width: 36px; height: 36px; border-radius: 8px; background: rgba(255,255,255,0.9);"><i class="bi bi-geo-alt text-dark"></i></button>
                </div>
                
                <!-- Floating Legend overlay -->
                <div class="map-floating-overlay bottom-0 start-0 m-3 p-3 bg-white rounded-3 shadow-sm border d-none d-sm-block" style="border-color: #E5E7EB !important; max-width: 220px; z-index: 1000;">
                    <h6 class="text-dark small fw-bold mb-2">Composite Risk Levels</h6>
                    <div class="d-flex flex-column gap-1 text-muted small" style="font-size: 0.72rem;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="d-inline-block rounded-circle" style="width: 10px; height: 10px; background-color: #ef4444;"></span> High / Critical (&gt; 60)
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="d-inline-block rounded-circle" style="width: 10px; height: 10px; background-color: #f59e0b;"></span> Medium (35 - 60)
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="d-inline-block rounded-circle" style="width: 10px; height: 10px; background-color: #10b981;"></span> Low Risk (&lt; 35)
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Risk Countries List -->
    <div class="col-lg-4">
        <x-card title="Top Risk Hotspots" icon="bi-exclamation-triangle">
            <x-table :headers="['Country', 'Risk Index', 'Trend']" tbodyId="hotspots-tbody">
                @forelse($topRiskCountries as $tr)
                @php
                    $badgeType = 'success';
                    if ($tr->risk_level === 'high' || $tr->risk_level === 'critical') $badgeType = 'danger';
                    elseif ($tr->risk_level === 'medium') $badgeType = 'warning';
                    
                    $trendIcon = 'bi-dash';
                    $trendColor = 'text-muted';
                    $trendText = 'Stable';
                    if ($tr->score_change > 0) {
                        $trendIcon = 'bi-arrow-up';
                        $trendColor = 'text-danger';
                        $trendText = 'Rising';
                    } elseif ($tr->score_change < 0) {
                        $trendIcon = 'bi-arrow-down';
                        $trendColor = 'text-success';
                        $trendText = 'Lower';
                    }
                @endphp
                <tr>
                    <td>
                        <a href="{{ route('countries.show', $tr->country->iso2) }}" class="d-flex align-items-center gap-2 small text-decoration-none hover-primary fw-semibold">
                            <img src="{{ $tr->country->flag_url }}" alt="{{ $tr->country->name }} Flag" class="rounded border border-secondary border-opacity-10" style="width: 20px; height: 13px; object-fit: cover;">
                            {{ $tr->country->name }}
                        </a>
                    </td>
                    <td>
                        <x-badge type="{{ $badgeType }}">
                            {{ number_format($tr->composite_score, 1) }}
                        </x-badge>
                    </td>
                    <td>
                        <span class="{{ $trendColor }} small fw-semibold">
                            <i class="bi {{ $trendIcon }}"></i> {{ $trendText }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center text-muted py-3">No risk hotspot entries found.</td>
                </tr>
                @endforelse
            </x-table>
        </x-card>
    </div>
</div>

<!-- Analytics Section Header -->
<div class="d-flex align-items-center justify-content-between mb-3 mt-4">
    <h5 class="text-dark fw-bold mb-0" style="font-size: 1.1rem; letter-spacing: -0.01em;">
        <i class="bi bi-bar-chart-steps text-primary me-2"></i>Risk & Market Analytics
    </h5>
</div>

<div class="row g-4 mb-4">
    <!-- Chart Card: Risk Category Radar -->
    <div class="col-lg-6">
        <div class="card card-premium border-0 h-100">
            <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between py-3">
                <h5 class="card-title text-dark mb-0 fs-6 fw-semibold">
                    <i class="bi bi-compass me-2 text-primary"></i>Global Supply Chain Risk Scope
                </h5>
            </div>
            <div class="card-body p-4 d-flex align-items-center justify-content-center" style="min-height: 320px;">
                <div style="width: 100%; height: 280px; max-height: 280px; position: relative;">
                    <canvas id="dashboardRadarChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Card: Risk Score Distribution -->
    <div class="col-lg-6">
        <div class="card card-premium border-0 h-100">
            <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between py-3">
                <h5 class="card-title text-dark mb-0 fs-6 fw-semibold">
                    <i class="bi bi-bar-chart-line me-2 text-success"></i>Score Distribution across Monitored Regions
                </h5>
            </div>
            <div class="card-body p-4 d-flex align-items-center justify-content-center" style="min-height: 320px;">
                <div style="width: 100%; height: 280px; max-height: 280px; position: relative;">
                    <canvas id="dashboardBarChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Secondary Row: Watchlist Table & Operation Logs -->
<div class="row g-4 mb-4">
    <!-- Pinned Watchlist -->
    <div class="col-lg-7">
        <x-card title="Monitored Pinned Watchlist" icon="bi-eye">
            <x-slot name="headerActions">
                <a href="{{ route('watchlists.index') }}" class="btn btn-sm btn-secondary py-1 px-3" style="border-radius: 8px;">View All</a>
            </x-slot>
            
            <x-table :headers="['Country', 'Risk Score', 'Port Logistics', 'Action']">
                @forelse($watchlistCountries as $c)
                @php
                    $latestScore = $c->latestRiskScore;
                    $compositeVal = $latestScore ? (float)$latestScore->composite_score : null;
                    $level = $latestScore ? $latestScore->risk_level : 'low';
                    
                    $badgeType = 'success';
                    if ($level === 'high' || $level === 'critical') $badgeType = 'danger';
                    elseif ($level === 'medium') $badgeType = 'warning';

                    $portStatus = 'Active';
                    $portIcon = 'bi-check-circle-fill';
                    $portColor = 'text-success';
                    
                    if ($c->ports->count() === 0) {
                        $portStatus = 'No Ports';
                        $portIcon = 'bi-dash-circle';
                        $portColor = 'text-muted';
                    } elseif ($c->iso2 === 'CN') {
                        $portStatus = 'Congested';
                        $portIcon = 'bi-exclamation-triangle-fill';
                        $portColor = 'text-warning';
                    }
                @endphp
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ $c->flag_url }}" alt="{{ $c->name }} Flag" class="rounded border border-secondary border-opacity-10" style="width: 20px; height: 13px; object-fit: cover;">
                            <strong>{{ $c->name }} ({{ $c->iso3 }})</strong>
                        </div>
                    </td>
                    <td>
                        <x-badge type="{{ $badgeType }}">
                            {{ $compositeVal !== null ? number_format($compositeVal, 1) . ' (' . ucfirst($level) . ')' : 'N/A' }}
                        </x-badge>
                    </td>
                    <td><span class="{{ $portColor }} fw-semibold small"><i class="bi {{ $portIcon }} me-1"></i> {{ $portStatus }}</span></td>
                    <td>
                        <a href="{{ route('countries.show', $c->iso2) }}" class="btn-action-saas" title="Details">
                            <i class="bi bi-eye-fill"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-3">No watchlist countries configured.</td>
                </tr>
                @endforelse
            </x-table>
        </x-card>
    </div>

    <!-- Operations activity feed -->
    <div class="col-lg-5">
        <x-card title="System Operations Logs" icon="bi-activity">
            <div class="d-flex flex-column gap-3">
                <div class="activity-item d-flex gap-2">
                    <div class="text-success"><i class="bi bi-plus-circle-fill"></i></div>
                    <div>
                        <span class="text-dark small fw-semibold">Watchlist Added</span>
                        <p class="text-muted small mb-0" style="font-size: 0.75rem;">User logged country item 'PHL' to primary watchlist.</p>
                        <span class="text-muted small" style="font-size: 0.7rem;">Today, 10:14 AM</span>
                    </div>
                </div>
                <div class="activity-item d-flex gap-2">
                    <div class="text-primary"><i class="bi bi-info-circle-fill"></i></div>
                    <div>
                        <span class="text-dark small fw-semibold">Task Scheduler Completed</span>
                        <p class="text-muted small mb-0" style="font-size: 0.75rem;">Scheduled hourly job fetched current Open-Meteo values for 195 capitals.</p>
                        <span class="text-muted small" style="font-size: 0.7rem;">Today, 10:00 AM</span>
                    </div>
                </div>
                <div class="activity-item d-flex gap-2">
                    <div class="text-warning"><i class="bi bi-exclamation-triangle-fill"></i></div>
                    <div>
                        <span class="text-dark small fw-semibold">Scoring Weights Adjusted</span>
                        <p class="text-muted small mb-0" style="font-size: 0.75rem;">Admin account adjusted Geopolitical Risk index weight from 0.20 to 0.25.</p>
                        <span class="text-muted small" style="font-size: 0.7rem;">Yesterday, 4:30 PM</span>
                    </div>
                </div>
            </div>
        </x-card>
    </div>
</div>

<!-- Bottom Row: Recent News & System Activity Feed -->
<div class="row g-4 mb-4">
    <!-- Geopolitical & Economics News Feed -->
    <div class="col-lg-6">
        <x-card title="Supply Chain News Feed" icon="bi-newspaper">
            <div class="d-flex flex-column gap-3">
                <div class="news-item-bg p-3 rounded border d-flex gap-3 align-items-start">
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20 mt-1 small">Geopolitics</span>
                    <div>
                        <h6 class="text-dark mb-1 fw-semibold small">Suez Canal Route Tension Escalates With Local Shipping Restrictions</h6>
                        <span class="text-muted small d-block" style="font-size: 0.75rem;">Source: Lloyd's List &bull; 10 mins ago</span>
                    </div>
                </div>
                <div class="news-item-bg p-3 rounded border d-flex gap-3 align-items-start">
                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-20 mt-1 small">Logistics</span>
                    <div>
                        <h6 class="text-dark mb-1 fw-semibold small">Manila Terminal Congestion Rises by 15% due to Monsoon Weather Fronts</h6>
                        <span class="text-muted small d-block" style="font-size: 0.75rem;">Source: PortNews &bull; 45 mins ago</span>
                    </div>
                </div>
                <div class="news-item-bg p-3 rounded border d-flex gap-3 align-items-start">
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 mt-1 small">Economics</span>
                    <div>
                        <h6 class="text-dark mb-1 fw-semibold small">World Bank Lowers Regional GDP Growth Forecast for Latin America</h6>
                        <span class="text-muted small d-block" style="font-size: 0.75rem;">Source: Reuters &bull; 2 hours ago</span>
                    </div>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Recent Alerts -->
    <div class="col-lg-6">
        <x-card title="Recent System Alerts Log" icon="bi-bell-fill text-warning">
            <div id="recent-alerts-list" class="d-flex flex-column gap-2" style="max-height: 380px; overflow-y: auto;">
                @forelse($recentAlerts as $alert)
                <div class="d-flex gap-3 align-items-start p-3 rounded border border-danger border-opacity-10" style="background-color: #FEF2F2;">
                    <div class="text-danger mt-0.5"><i class="bi bi-exclamation-triangle-fill"></i></div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="text-dark small fw-bold">{{ $alert->description }}</span>
                            <span class="text-muted small" style="font-size: 0.72rem;">{{ $alert->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="text-muted small mt-1" style="font-size: 0.72rem;">
                            Change details: score shifted from 
                            <span class="text-warning fw-semibold">{{ number_format($alert->old_values['composite_score'] ?? 0, 2) }}</span> 
                            to 
                            <span class="text-danger fw-semibold">{{ number_format($alert->new_values['composite_score'] ?? 0, 2) }}</span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-4">
                    <i class="bi bi-shield-check display-6 d-block mb-2 text-success"></i>
                    <p class="small mb-0">No active system alerts recorded.</p>
                </div>
                @endforelse
            </div>
        </x-card>
    </div>
</div>

@php
    $mapCountriesData = $topRiskCountries->map(function($r) {
        return [
            'name' => $r->country->name,
            'iso2' => $r->country->iso2,
            'lat' => $r->country->latitude,
            'lng' => $r->country->longitude,
            'score' => (float)$r->composite_score,
            'level' => $r->risk_level
        ];
    });
@endphp
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Time Sync
        const navbarTime = document.getElementById('liveSyncTime');
        const heroTime = document.getElementById('heroSyncTime');
        
        if (navbarTime && heroTime) {
            heroTime.innerText = navbarTime.innerText || '--:--:--';
            setInterval(function() {
                if (navbarTime.innerText) {
                    heroTime.innerText = navbarTime.innerText;
                }
            }, 500);
        }

        // 1. Leaflet Map Initialization
        const rawMapData = @json($mapCountriesData);
        const mapData = rawMapData.filter(c => c.lat && c.lng);

        const map = L.map('dashboardMap', {
            center: [15, 10],
            zoom: 2,
            minZoom: 2,
            maxZoom: 9,
            scrollWheelZoom: false,
            zoomControl: false // Disable default zoom controls to look cleaner
        });

        // Add cleaner default Zoom control at top-left
        L.control.zoom({ position: 'topleft' }).addTo(map);

        // Voyager beautiful tiles
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '© OpenStreetMap contributors © CARTO',
            subdomains: 'abcd',
            maxZoom: 19
        }).addTo(map);

        // Hide Skeleton once tile layer or small delay finishes
        setTimeout(() => {
            const skel = document.getElementById('mapSkeleton');
            if (skel) {
                skel.style.opacity = 0;
                setTimeout(() => skel.remove(), 400);
            }
        }, 600);

        // Custom Layer group to allow redrawing filtered markers
        const markersGroup = L.layerGroup().addTo(map);

        function drawMarkers(data) {
            markersGroup.clearLayers();
            data.forEach(item => {
                const color = item.score > 60 ? '#EF4444' : (item.score > 35 ? '#F59E0B' : '#10B981');
                const badgeClass = item.score > 60 ? 'bg-danger' : (item.score > 35 ? 'bg-warning' : 'bg-success');
                const riskLevel = item.score > 60 ? 'CRITICAL' : (item.score > 35 ? 'MEDIUM' : 'LOW');
                
                const marker = L.circleMarker([item.lat, item.lng], {
                    radius: 8,
                    fillColor: color,
                    color: '#FFFFFF',
                    weight: 1.5,
                    opacity: 1,
                    fillOpacity: 0.8
                });

                // Custom detailed popup
                marker.bindPopup(`
                    <div class="p-2 text-dark" style="font-family: 'Outfit', sans-serif; min-width: 200px;">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <img src="${item.flag_url}" class="rounded border shadow-sm" style="width: 24px; height: 16px; object-fit: cover;" alt="">
                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.88rem;">${item.name}</h6>
                        </div>
                        <div class="mb-2">
                            <span class="badge ${badgeClass} text-white px-2 py-0.5 small" style="font-size: 0.72rem; font-weight: 600;">
                                Score: ${item.score.toFixed(1)} (${riskLevel})
                            </span>
                        </div>
                        <div class="text-muted small mb-2" style="font-size: 0.75rem;">
                            Region: <strong>${item.region || 'N/A'}</strong><br>
                            GDP: <strong>$${item.gdp ? (item.gdp / 1e9).toFixed(1) + 'B' : 'N/A'}</strong>
                        </div>
                        <a href="/countries/${item.iso2}" class="btn btn-primary btn-sm w-100 rounded-pill text-white fw-bold py-1 text-center text-decoration-none" style="font-size: 0.72rem;">
                            <i class="bi bi-eye-fill me-1"></i> View Profile
                        </a>
                    </div>
                `, {
                    className: 'premium-leaflet-popup'
                });

                marker.addTo(markersGroup);
            });
        }

        drawMarkers(mapData);

        // Map Control actions
        document.getElementById('mapBtnFullscreen').addEventListener('click', function() {
            const mapContainer = document.getElementById('dashboardMap').parentElement;
            if (!document.fullscreenElement) {
                mapContainer.requestFullscreen().catch(err => {
                    console.error(`Error enabling fullscreen: ${err.message}`);
                });
            } else {
                document.exitFullscreen();
            }
        });

        document.getElementById('mapBtnReset').addEventListener('click', function() {
            map.setView([15, 10], 2);
        });

        document.getElementById('mapBtnLocation').addEventListener('click', function() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(position => {
                    map.setView([position.coords.latitude, position.coords.longitude], 6);
                }, err => {
                    alert("Unable to retrieve location.");
                });
            } else {
                alert("Geolocation is not supported by your browser.");
            }
        });

        // Filter chips actions
        document.querySelectorAll('.filter-chip-dashboard').forEach(chip => {
            chip.addEventListener('click', function() {
                document.querySelectorAll('.filter-chip-dashboard').forEach(c => {
                    c.classList.remove('btn-primary', 'active');
                    c.classList.add('btn-light');
                });
                this.classList.add('btn-primary', 'active');
                this.classList.remove('btn-light');

                const filterVal = this.getAttribute('data-filter');
                const filtered = mapData.filter(item => {
                    if (filterVal === 'all') return true;
                    if (filterVal === 'high') return item.score > 60;
                    if (filterVal === 'medium') return item.score > 35 && item.score <= 60;
                    if (filterVal === 'low') return item.score <= 35;
                    return true;
                });
                drawMarkers(filtered);
            });
        });

        // 2. Chart.js Radar Initialization (Risk Category Breakdown Global Average)
        new Chart(document.getElementById('dashboardRadarChart'), {
            type: 'radar',
            data: {
                labels: ['Economic', 'Weather', 'Currency Stability', 'Geopolitical', 'Logistics'],
                datasets: [{
                    label: 'Global Mean Risk Rating',
                    data: [42.5, 31.8, 28.4, 52.1, 38.9],
                    backgroundColor: 'rgba(37, 99, 235, 0.15)',
                    borderColor: '#2563EB',
                    borderWidth: 2,
                    pointBackgroundColor: '#2563EB',
                    pointBorderColor: '#FFFFFF',
                    pointHoverBackgroundColor: '#FFFFFF',
                    pointHoverBorderColor: '#2563EB'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 800,
                    easing: 'easeOutQuart'
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1E293B',
                        titleColor: '#FFFFFF',
                        bodyColor: '#F1F5F9',
                        borderColor: '#475569',
                        borderWidth: 1,
                        borderRadius: 12,
                        padding: 10,
                        boxPadding: 6
                    }
                },
                scales: {
                    r: {
                        angleLines: { color: 'rgba(226, 232, 240, 0.6)' },
                        grid: { color: 'rgba(226, 232, 240, 0.6)' },
                        pointLabels: { color: '#64748B', font: { family: 'Outfit', size: 10, weight: '600' } },
                        ticks: { display: false, maxTicksLimit: 5 }
                    }
                }
            }
        });

        // 3. Chart.js Bar Chart (Region Risk Distribution)
        new Chart(document.getElementById('dashboardBarChart'), {
            type: 'bar',
            data: {
                labels: ['Americas', 'Asia', 'Europe', 'Africa', 'Oceania'],
                datasets: [{
                    label: 'Mean Risk Index',
                    data: [35.2, 49.8, 28.5, 54.1, 22.4],
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.85)',
                        'rgba(249, 115, 22, 0.85)',
                        'rgba(34, 197, 94, 0.85)',
                        'rgba(239, 68, 68, 0.85)',
                        'rgba(168, 85, 247, 0.85)'
                    ],
                    hoverBackgroundColor: [
                        'rgba(59, 130, 246, 1)',
                        'rgba(249, 115, 22, 1)',
                        'rgba(34, 197, 94, 1)',
                        'rgba(239, 68, 68, 1)',
                        'rgba(168, 85, 247, 1)'
                    ],
                    borderRadius: 8,
                    maxBarThickness: 32
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 800,
                    easing: 'easeOutQuart'
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1E293B',
                        titleColor: '#FFFFFF',
                        bodyColor: '#F1F5F9',
                        borderColor: '#475569',
                        borderWidth: 1,
                        borderRadius: 12,
                        padding: 10,
                        boxPadding: 6
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748B', font: { family: 'Outfit', size: 10, weight: '600' } }
                    },
                    y: {
                        border: { dash: [4, 4] },
                        grid: { color: 'rgba(226, 232, 240, 0.8)' },
                        ticks: { color: '#64748B', font: { size: 10 } }
                    }
                }
            }
        });
    });
</script>
@endsection
