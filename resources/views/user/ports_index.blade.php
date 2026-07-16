@extends('layouts.app')

@section('title', 'Ports & Logistics | Global Intelligence')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
<style>
    /* Premium Page Background & Typography */
    body {
        background-color: #F8FAFC !important;
        font-family: 'Inter', 'Outfit', sans-serif;
    }

    /* Metric Cards Styling */
    .metric-card-premium {
        background: #FFFFFF;
        border: 1px solid #E5E7EB;
        border-radius: 24px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.01);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .metric-card-premium:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 20px -8px rgba(0, 0, 0, 0.08);
    }

    /* Leaflet Map Hero & Glassmorphism Panels */
    .map-container-hero {
        position: relative;
        height: 520px;
        border-radius: 24px;
        overflow: hidden;
        border: 1px solid #E5E7EB;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.03);
    }
    #map {
        height: 100%;
        width: 100%;
        z-index: 1;
    }

    .glass-panel {
        position: absolute;
        z-index: 1000;
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(16px) saturate(180%);
        -webkit-backdrop-filter: blur(16px) saturate(180%);
        border: 1px solid rgba(255, 255, 255, 0.5);
        border-radius: 24px;
        padding: 16px;
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.06);
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease;
        pointer-events: auto;
    }
    .glass-panel:hover {
        transform: scale(1.02) translateY(-2px);
        box-shadow: 0 12px 40px 0 rgba(31, 38, 135, 0.1);
    }

    /* Specific Glass Panel Positions */
    .panel-left-top {
        top: 20px;
        left: 20px;
        max-width: 280px;
    }
    .panel-right-top {
        top: 20px;
        right: 20px;
        min-width: 200px;
    }
    .panel-bottom-left {
        bottom: 20px;
        left: 20px;
        min-width: 220px;
    }
    .panel-bottom-right {
        bottom: 20px;
        right: 20px;
        min-width: 220px;
    }

    /* Floating Map Zoom & Action Controls */
    .custom-map-controls {
        position: absolute;
        right: 20px;
        top: 160px;
        z-index: 1000;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .map-btn-custom {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: #FFFFFF;
        border: 1px solid #E5E7EB;
        color: #1E293B;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .map-btn-custom:hover {
        background: #F8FAFC;
        transform: translateY(-2px);
        color: #2563EB;
        border-color: #CBD5E1;
    }

    /* Pulse Live Indicator */
    .live-indicator-dot {
        width: 10px;
        height: 10px;
        background-color: #10B981;
        border-radius: 50%;
        display: inline-block;
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        animation: pulse-green 2s infinite;
    }
    @keyframes pulse-green {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }

    /* Autocomplete Dropdown styling */
    .autocomplete-wrapper {
        position: relative;
    }
    .autocomplete-dropdown {
        position: absolute;
        top: 105%;
        left: 0;
        right: 0;
        background: #FFFFFF;
        border: 1px solid #E5E7EB;
        border-radius: 16px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08);
        z-index: 10000;
        max-height: 250px;
        overflow-y: auto;
        display: none;
    }
    .autocomplete-item {
        padding: 12px 16px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: background 0.15s ease;
    }
    .autocomplete-item:hover, .autocomplete-item.active {
        background-color: #F1F5F9;
    }
    .autocomplete-item strong {
        color: #0F172A;
    }

    /* Accordion Filter Panel */
    .filter-accordion-header {
        background-color: transparent !important;
        border: none !important;
        padding: 12px 0;
        font-weight: 600;
        color: #334155;
        font-size: 0.85rem;
    }
    .filter-accordion-header:after {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%2364748b'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e") !important;
    }
    .filter-accordion-item {
        border: none !important;
        border-bottom: 1px solid #F1F5F9 !important;
    }

    /* Active Filter Chips */
    .filter-chip-badge {
        background-color: #EFF6FF;
        color: #1E40AF;
        border: 1px solid #DBEAFE;
        border-radius: 12px;
        padding: 6px 12px;
        font-size: 0.78rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.15s ease;
    }
    .filter-chip-badge:hover {
        background-color: #DBEAFE;
    }
    .filter-chip-close {
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        width: 14px;
        height: 14px;
        background-color: rgba(30, 64, 175, 0.1);
        color: #1E40AF;
        font-size: 0.65rem;
        transition: background 0.15s;
    }
    .filter-chip-close:hover {
        background-color: rgba(30, 64, 175, 0.25);
    }

    /* Premium Leaflet Popup design overrides */
    .premium-leaflet-popup .leaflet-popup-content-wrapper {
        border-radius: 20px !important;
        border: 1px solid #E2E8F0 !important;
        padding: 4px !important;
    }
    .premium-leaflet-popup .leaflet-popup-content {
        margin: 12px !important;
    }
    .btn-popup-gradient {
        background: linear-gradient(135deg, #2563EB, #06B6D4) !important;
        border: none !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease !important;
    }
    .btn-popup-gradient:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3) !important;
    }

    /* Directory Table rounded and sticky features */
    .table-container-premium {
        background: #FFFFFF;
        border: 1px solid #E5E7EB;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.01);
    }
    .table-premium thead {
        position: sticky;
        top: 0;
        z-index: 10;
        background-color: #F8FAFC;
    }
    .table-premium tbody tr {
        transition: background-color 0.15s ease, transform 0.15s ease;
    }
    .table-premium tbody tr:hover {
        background-color: #EFF6FF !important;
        cursor: pointer;
    }
    .table-premium tr.active-row {
        background-color: #DBEAFE !important;
        font-weight: 500;
    }

    /* Small Risk Progress Bar */
    .risk-progress-bar {
        height: 6px;
        border-radius: 3px;
        background-color: #E2E8F0;
        overflow: hidden;
    }
    .risk-progress-fill {
        height: 100%;
        border-radius: 3px;
        transition: width 0.3s ease;
    }

    /* Shimmer Skeleton elements */
    .shimmer-skeleton-row td {
        position: relative;
        overflow: hidden;
    }
    .shimmer-skeleton-row td::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.6), transparent);
        transform: translateX(-100%);
        animation: loading-shimmer 1.5s infinite;
    }
    @keyframes loading-shimmer {
        100% { transform: translateX(100%); }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}" class="text-decoration-none text-muted small"><i class="bi bi-house-door me-1"></i>Home</a></li>
            <li class="breadcrumb-item text-primary small active" aria-current="page">Ports & Logistics</li>
        </ol>
    </nav>

    <!-- Header Title -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h3 text-dark mb-1 fw-bold" style="font-family: 'Outfit', sans-serif; letter-spacing: -0.5px;">Ports & Logistics Intelligence</h1>
            <p class="text-muted mb-0 small">Enterprise maritime infrastructure directory and risk analysis console</p>
        </div>
        <div>
            <span class="badge bg-white text-dark border border-light-subtle py-2.5 px-3.5 shadow-sm rounded-pill d-inline-flex align-items-center gap-2" style="font-size: 0.85rem;">
                <span class="live-indicator-dot"></span>
                <span class="fw-semibold">Active Ports Tracked: {{ count($mapPorts) }}</span>
            </span>
        </div>
    </div>

    <!-- 4 Statistic Overview Cards (V8 Style) -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="metric-card-premium p-4 d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; flex-shrink: 0;">
                    <i class="bi bi-anchor fs-4"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Total Global Ports</span>
                    <h3 class="fw-bold mb-0 text-dark" style="font-family: 'Outfit', sans-serif;">4,747+</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-card-premium p-4 d-flex align-items-center gap-3">
                <div class="rounded-circle bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; flex-shrink: 0;">
                    <i class="bi bi-globe fs-4"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Monitored Countries</span>
                    <h3 class="fw-bold mb-0 text-dark" style="font-family: 'Outfit', sans-serif;">150+</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-card-premium p-4 d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; flex-shrink: 0;">
                    <i class="bi bi-shield-check fs-4"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Major Cargo Hubs</span>
                    <h3 class="fw-bold mb-0 text-dark" style="font-family: 'Outfit', sans-serif;">1,280+</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-card-premium p-4 d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; flex-shrink: 0;">
                    <i class="bi bi-graph-up fs-4"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Average Risk Score</span>
                    <h3 class="fw-bold mb-0 text-dark" style="font-family: 'Outfit', sans-serif;">52.8</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet Map Hero Area with Glassmorphism Overlays -->
    <div class="map-container-hero mb-4">
        <!-- Map skeleton loader -->
        <div id="mapSkeleton" class="position-absolute top-0 start-0 w-100 h-100 bg-white d-flex flex-column gap-3 p-4" style="z-index: 10001; transition: opacity 0.4s ease;">
            <div class="skeleton-shimmer rounded-3" style="height: 32px; width: 30%;"></div>
            <div class="skeleton-shimmer rounded-4 flex-grow-1"></div>
        </div>

        <!-- Leaflet Map Div -->
        <div id="map"></div>

        <!-- LEFT TOP: Global Ports Intelligence Glass Panel -->
        <div class="glass-panel panel-left-top d-none d-md-block">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-compass-fill text-primary fs-5"></i>
                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.9rem;">Global Ports Intelligence</h6>
            </div>
            <div class="text-muted small mb-2" style="font-size: 0.75rem;">
                Monitoring maritime supply chain assets and critical shipping bottlenecks.
            </div>
            <div class="d-flex align-items-center gap-3 mt-2 border-top pt-2" style="border-color: rgba(0,0,0,0.05) !important;">
                <div>
                    <span class="text-dark fw-bold d-block" style="font-size: 0.9rem;">4,747+</span>
                    <span class="text-muted" style="font-size: 0.65rem;">World Ports</span>
                </div>
                <div>
                    <span class="text-dark fw-bold d-block" style="font-size: 0.9rem;">150+</span>
                    <span class="text-muted" style="font-size: 0.65rem;">Countries</span>
                </div>
            </div>
        </div>

        <!-- RIGHT TOP: LIVE Feed Panel -->
        <div class="glass-panel panel-right-top d-none d-md-block">
            <div class="d-flex align-items-center justify-content-between gap-3 mb-2">
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1.5 fw-bold" style="font-size: 0.65rem;">
                    <span class="live-indicator-dot" style="width: 7px; height: 7px;"></span> LIVE
                </span>
                <span class="text-muted" style="font-size: 0.68rem;">Real-time Monitoring</span>
            </div>
            <div class="text-muted small mt-1" style="font-size: 0.72rem;">
                <i class="bi bi-shield-check text-success me-1"></i>Marker Cluster Active
            </div>
        </div>

        <!-- BOTTOM LEFT: Map Legend -->
        <div class="glass-panel panel-bottom-left d-none d-sm-block">
            <h6 class="text-dark small fw-bold mb-2" style="font-size: 0.78rem;">Composite Risk Level</h6>
            <div class="d-flex flex-column gap-1.5 text-muted" style="font-size: 0.7rem;">
                <div class="d-flex align-items-center justify-content-between">
                    <span class="d-flex align-items-center gap-1.5"><span class="d-inline-block rounded-circle" style="width: 8px; height: 8px; background-color: #10B981;"></span> Low Risk</span>
                    <span class="fw-bold">&lt; 40.0</span>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="d-flex align-items-center gap-1.5"><span class="d-inline-block rounded-circle" style="width: 8px; height: 8px; background-color: #F59E0B;"></span> Medium Risk</span>
                    <span class="fw-bold">40.0 - 70.0</span>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="d-flex align-items-center gap-1.5"><span class="d-inline-block rounded-circle" style="width: 8px; height: 8px; background-color: #EF4444;"></span> High Risk</span>
                    <span class="fw-bold">&gt; 70.0</span>
                </div>
            </div>
        </div>

        <!-- BOTTOM RIGHT: Map Mini Metrics -->
        <div class="glass-panel panel-bottom-right">
            <h6 class="text-dark small fw-bold mb-2" style="font-size: 0.78rem;">Viewport Telemetry</h6>
            <div class="d-flex flex-column gap-1 text-muted" style="font-size: 0.7rem;">
                <div class="d-flex justify-content-between">
                    <span>Current Zoom:</span>
                    <strong class="text-dark" id="telemetryZoom">2</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Visible Ports:</span>
                    <strong class="text-dark" id="telemetryVisible">Calculating...</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Cluster Status:</span>
                    <strong class="text-success">Active</strong>
                </div>
            </div>
        </div>

        <!-- Floating Action Map Controls -->
        <div class="custom-map-controls">
            <button id="mapBtnZoomIn" class="map-btn-custom" title="Zoom In"><i class="bi bi-plus-lg"></i></button>
            <button id="mapBtnZoomOut" class="map-btn-custom" title="Zoom Out"><i class="bi bi-dash-lg"></i></button>
            <button id="mapBtnFullscreenCustom" class="map-btn-custom" title="Fullscreen Toggle"><i class="bi bi-fullscreen"></i></button>
            <button id="mapBtnResetCustom" class="map-btn-custom" title="Reset View"><i class="bi bi-arrow-counterclockwise"></i></button>
            <button id="mapBtnLocationCustom" class="map-btn-custom" title="My Coordinates"><i class="bi bi-geo-alt"></i></button>
        </div>
    </div>

    <!-- Active Filter Chips display area -->
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3 px-1" id="filter-chips-container" style="min-height: 38px;">
        <!-- Generated dynamically via JS -->
    </div>

    <!-- Main Sidebar and Grid layout -->
    <div class="row g-4">
        <!-- Sidebar Filter controls (Sleek Accordion Panel) -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm" style="border-radius: 24px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center" style="border-color: #E5E7EB !important;">
                    <h5 class="card-title text-dark mb-0 fs-6 fw-bold">
                        <i class="bi bi-funnel me-2 text-primary"></i>Filters & Controls
                    </h5>
                    <button type="button" id="btn-reset-filters-panel" class="btn btn-link p-0 text-muted text-decoration-none small" style="font-size: 0.78rem;"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset</button>
                </div>
                <div class="card-body">
                    <form action="{{ route('ports.index') }}" method="GET" id="ports-filter-form">
                        <div class="accordion" id="accordionFilter">
                            
                            <!-- Section: General Search & Autocomplete -->
                            <div class="accordion-item filter-accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button filter-accordion-header shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGeneral" aria-expanded="true">
                                        <i class="bi bi-search me-2 text-muted"></i>GENERAL SEARCH
                                    </button>
                                </h2>
                                <div id="collapseGeneral" class="accordion-collapse collapse show" data-bs-parent="#accordionFilter">
                                    <div class="accordion-body px-0 pt-0 pb-3">
                                        <div class="autocomplete-wrapper">
                                            <div class="input-group">
                                                <span class="input-group-text bg-light text-muted border-end-0" style="border-radius: 16px 0 0 16px;"><i class="bi bi-search"></i></span>
                                                <input type="text" name="search" id="inputSearchQuery" class="form-control bg-light border-start-0 border-light-subtle" placeholder="Port, Country, LOCODE..." value="{{ $filters['search'] ?? '' }}" autocomplete="off" style="border-radius: 0 16px 16px 0; font-size: 0.85rem;">
                                            </div>
                                            <!-- Autocomplete Results list -->
                                            <div class="autocomplete-dropdown" id="searchAutocompleteDropdown"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section: Location filters -->
                            <div class="accordion-item filter-accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button filter-accordion-header collapsed shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLocation">
                                        <i class="bi bi-geo-alt me-2 text-muted"></i>LOCATION SCOPE
                                    </button>
                                </h2>
                                <div id="collapseLocation" class="accordion-collapse collapse" data-bs-parent="#accordionFilter">
                                    <div class="accordion-body px-0 pt-0 pb-3 d-flex flex-column gap-3">
                                        <div>
                                            <label class="form-label text-muted small fw-semibold">Geographical Region</label>
                                            <select name="region" class="form-select border-light-subtle" style="border-radius: 16px; font-size: 0.85rem;">
                                                <option value="all">All Regions</option>
                                                <option value="Americas" {{ ($filters['region'] ?? '') === 'Americas' ? 'selected' : '' }}>Americas</option>
                                                <option value="Asia" {{ ($filters['region'] ?? '') === 'Asia' ? 'selected' : '' }}>Asia</option>
                                                <option value="Europe" {{ ($filters['region'] ?? '') === 'Europe' ? 'selected' : '' }}>Europe</option>
                                                <option value="Africa" {{ ($filters['region'] ?? '') === 'Africa' ? 'selected' : '' }}>Africa</option>
                                                <option value="Oceania" {{ ($filters['region'] ?? '') === 'Oceania' ? 'selected' : '' }}>Oceania</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="form-label text-muted small fw-semibold">Sovereign Country</label>
                                            <select name="country_id" class="form-select border-light-subtle" style="border-radius: 16px; font-size: 0.85rem;">
                                                <option value="all">All Countries</option>
                                                @foreach($countries as $c)
                                                    <option value="{{ $c->id }}" {{ ($filters['country_id'] ?? '') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section: Harbor Attributes -->
                            <div class="accordion-item filter-accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button filter-accordion-header collapsed shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHarbor">
                                        <i class="bi bi-box me-2 text-muted"></i>HARBOR SPECIFICATIONS
                                    </button>
                                </h2>
                                <div id="collapseHarbor" class="accordion-collapse collapse" data-bs-parent="#accordionFilter">
                                    <div class="accordion-body px-0 pt-0 pb-3 d-flex flex-column gap-3">
                                        <div>
                                            <label class="form-label text-muted small fw-semibold">Harbor Size</label>
                                            <select name="harbor_size" class="form-select border-light-subtle" style="border-radius: 16px; font-size: 0.85rem;">
                                                <option value="all">All Sizes</option>
                                                <option value="Very Small" {{ ($filters['harbor_size'] ?? '') === 'Very Small' ? 'selected' : '' }}>Very Small</option>
                                                <option value="Small" {{ ($filters['harbor_size'] ?? '') === 'Small' ? 'selected' : '' }}>Small</option>
                                                <option value="Medium" {{ ($filters['harbor_size'] ?? '') === 'Medium' ? 'selected' : '' }}>Medium</option>
                                                <option value="Large" {{ ($filters['harbor_size'] ?? '') === 'Large' ? 'selected' : '' }}>Large</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="form-label text-muted small fw-semibold">Harbor Type</label>
                                            <select name="harbor_type" class="form-select border-light-subtle" style="border-radius: 16px; font-size: 0.85rem;">
                                                <option value="all">All Types</option>
                                                <option value="Coastal Natural" {{ ($filters['harbor_type'] ?? '') === 'Coastal Natural' ? 'selected' : '' }}>Coastal Natural</option>
                                                <option value="Coastal Breakwater" {{ ($filters['harbor_type'] ?? '') === 'Coastal Breakwater' ? 'selected' : '' }}>Coastal Breakwater</option>
                                                <option value="Coastal Tide Gate" {{ ($filters['harbor_type'] ?? '') === 'Coastal Tide Gate' ? 'selected' : '' }}>Coastal Tide Gate</option>
                                                <option value="River Natural" {{ ($filters['harbor_type'] ?? '') === 'River Natural' ? 'selected' : '' }}>River Natural</option>
                                                <option value="River Basin" {{ ($filters['harbor_type'] ?? '') === 'River Basin' ? 'selected' : '' }}>River Basin</option>
                                                <option value="Canal" {{ ($filters['harbor_type'] ?? '') === 'Canal' ? 'selected' : '' }}>Canal</option>
                                                <option value="Lake or Canal" {{ ($filters['harbor_type'] ?? '') === 'Lake or Canal' ? 'selected' : '' }}>Lake or Canal</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section: Operational Status -->
                            <div class="accordion-item filter-accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button filter-accordion-header collapsed shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapseStatus">
                                        <i class="bi bi-activity me-2 text-muted"></i>OPERATIONAL STATUS
                                    </button>
                                </h2>
                                <div id="collapseStatus" class="accordion-collapse collapse" data-bs-parent="#accordionFilter">
                                    <div class="accordion-body px-0 pt-0 pb-3">
                                        <label class="form-label text-muted small fw-semibold">Status Scope</label>
                                        <select name="status" class="form-select border-light-subtle" style="border-radius: 16px; font-size: 0.85rem;">
                                            <option value="all">All Statuses</option>
                                            <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Active / Operational</option>
                                            <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Inactive / Suspended</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Directory list (Sleek card & progress indicators) -->
        <div class="col-lg-9">
            <div class="table-container-premium shadow-sm">
                <div class="card-header bg-transparent border-bottom py-3" style="border-color: #E5E7EB !important;">
                    <h5 class="card-title text-dark mb-0 fs-6 fw-bold">
                        <i class="bi bi-list-columns-reverse me-2 text-primary"></i>Maritime Directory Registry
                    </h5>
                </div>
                <div class="card-body p-0">
                    <x-table class="table-premium mb-0" :headers="['Port Name', 'Code', 'Country', 'Coordinates', 'Type', 'Size', 'Risk Rating', 'Action']">
                        @include('user.partials.ports_table_rows')
                    </x-table>
                </div>

                <!-- Pagination footer container -->
                <div class="card-footer bg-transparent border-top py-3.5 d-flex justify-content-between align-items-center" style="border-color: #E5E7EB !important; {{ $ports->total() > 0 ? '' : 'display: none !important;' }}">
                    <div class="text-muted small pagination-info fw-semibold" style="font-size: 0.78rem;">
                        Showing {{ $ports->firstItem() }} to {{ $ports->lastItem() }} of {{ $ports->total() }} ports
                    </div>
                    <div class="pagination-links">
                        {{ $ports->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
<script>
    // Initialize map
    const map = L.map('map', {
        minZoom: 2,
        maxZoom: 18,
        zoomControl: false
    }).setView([20, 0], 2);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 20
    }).addTo(map);

    // Hide Skeleton once tile layer finishes loading
    setTimeout(() => {
        const skel = document.getElementById('mapSkeleton');
        if (skel) {
            skel.style.opacity = 0;
            setTimeout(() => skel.remove(), 400);
        }
    }, 600);

    // Custom Marker Cluster Group with premium settings
    const markersGroup = L.markerClusterGroup({
        showCoverageOnHover: false,
        zoomToBoundsOnClick: true,
        spiderfyOnMaxZoom: true,
        maxClusterRadius: 50
    }).addTo(map);

    // Keep track of markers in an object
    const markers = {};

    // Load active ports from backend
    const ports = @json($mapPorts);

    function drawMarkers(data) {
        markersGroup.clearLayers();
        Object.keys(markers).forEach(k => delete markers[k]);

        data.forEach(port => {
            if (port.latitude && port.longitude) {
                // Determine risk badge color
                let riskClass = 'bg-secondary text-muted border-secondary';
                let riskText = 'N/A';
                if (port.risk_score) {
                    const score = parseFloat(port.risk_score);
                    if (score < 40) {
                        riskClass = 'bg-success text-success border-success';
                        riskText = 'Low Risk';
                    } else if (score < 70) {
                        riskClass = 'bg-warning text-warning border-warning';
                        riskText = 'Medium Risk';
                    } else {
                        riskClass = 'bg-danger text-danger border-danger';
                        riskText = 'High Risk';
                    }
                }

                const flagEmoji = port.country_code 
                    ? `<span class="fs-6 me-1">${String.fromCodePoint(...[...port.country_code.toUpperCase()].map(c => 127397 + c.charCodeAt(0)))}</span>`
                    : '';

                const popupHtml = `
                    <div class="p-2 text-dark" style="font-family: 'Outfit', sans-serif; min-width: 260px;">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-2 pb-2 border-bottom" style="border-color: #E2E8F0 !important;">
                            <div class="d-flex align-items-center gap-1.5">
                                ${flagEmoji}
                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.92rem;">${port.name}</h6>
                            </div>
                            <span class="badge ${riskClass} bg-opacity-10 border small fw-bold px-2 py-0.5 rounded-pill" style="font-size: 0.68rem;">${riskText}</span>
                        </div>
                        <div class="text-muted small mb-3" style="font-size: 0.76rem; line-height: 1.6;">
                            <div class="d-flex justify-content-between mb-1"><span>Country:</span><strong class="text-dark">${port.country_name} (${port.country_code})</strong></div>
                            <div class="d-flex justify-content-between mb-1"><span>UN LOCODE:</span><strong class="text-dark"><code>${port.un_locode || 'N/A'}</code></strong></div>
                            <div class="d-flex justify-content-between mb-1"><span>Coordinates:</span><strong class="text-dark">${port.latitude.toFixed(4)}, ${port.longitude.toFixed(4)}</strong></div>
                            <div class="d-flex justify-content-between mb-1"><span>Harbor Size:</span><strong class="text-dark">${port.harbor_size || 'N/A'}</strong></div>
                            <div class="d-flex justify-content-between mb-1"><span>Harbor Type:</span><strong class="text-dark">${port.harbor_type || 'N/A'}</strong></div>
                            <div class="d-flex justify-content-between"><span>Max Vessel Size:</span><strong class="text-dark">${port.max_vessel_size || 'N/A'}</strong></div>
                        </div>
                        <a href="${port.view_url}" class="btn btn-popup-gradient btn-sm w-100 rounded-pill text-white fw-bold py-1.5 text-center text-decoration-none d-block" style="font-size: 0.76rem;">
                            <i class="bi bi-arrow-up-right-circle me-1"></i>View Country Dashboard
                        </a>
                    </div>
                `;

                // Build custom leaflet marker
                const marker = L.marker([port.latitude, port.longitude])
                    .bindPopup(popupHtml, { className: 'premium-leaflet-popup' });
                
                markersGroup.addLayer(marker);
                markers[`${port.latitude}_${port.longitude}`] = marker;
            }
        });

        // Update Visible Ports count initially
        updateVisibleCount();
    }

    // Draw initial markers
    drawMarkers(ports);

    // Update telemetry visible count based on current bounding box
    function updateVisibleCount() {
        let count = 0;
        const bounds = map.getBounds();
        ports.forEach(p => {
            if (p.latitude && p.longitude) {
                const latLng = L.latLng(p.latitude, p.longitude);
                if (bounds.contains(latLng)) {
                    count++;
                }
            }
        });
        const visibleEl = document.getElementById('telemetryVisible');
        if (visibleEl) visibleEl.innerText = `${count} Ports`;
    }

    // Map Event Listeners for Telemetry
    map.on('zoomend', () => {
        const zoomEl = document.getElementById('telemetryZoom');
        if (zoomEl) zoomEl.innerText = map.getZoom();
        updateVisibleCount();
    });
    map.on('moveend', () => {
        updateVisibleCount();
    });

    // Helper function to focus map on a port with flyTo bounce and open popup
    function focusMap(lat, lng, name) {
        map.flyTo([lat, lng], 11, {
            animate: true,
            duration: 1.5
        });
        const markerKey = `${lat}_${lng}`;
        
        map.once('moveend', () => {
            if (markers[markerKey]) {
                markers[markerKey].openPopup();
                
                // Highlight row in list
                document.querySelectorAll('.table-premium tbody tr').forEach(row => {
                    row.classList.remove('active-row');
                    if (row.innerHTML.includes(name)) {
                        row.classList.add('active-row');
                        row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                });
            }
        });

        // Scroll map into view for mobile
        document.getElementById('map').scrollIntoView({ behavior: 'smooth' });
    }

    // Floating Custom Control Actions
    document.getElementById('mapBtnZoomIn').addEventListener('click', () => map.zoomIn());
    document.getElementById('mapBtnZoomOut').addEventListener('click', () => map.zoomOut());

    document.getElementById('mapBtnFullscreenCustom').addEventListener('click', function() {
        const mapContainer = document.getElementById('map').parentElement;
        if (!document.fullscreenElement) {
            mapContainer.requestFullscreen().catch(err => {
                console.error(`Error enabling fullscreen: ${err.message}`);
            });
        } else {
            document.exitFullscreen();
        }
    });

    document.getElementById('mapBtnResetCustom').addEventListener('click', function() {
        map.setView([20, 0], 2);
    });

    document.getElementById('mapBtnLocationCustom').addEventListener('click', function() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(position => {
                map.flyTo([position.coords.latitude, position.coords.longitude], 10);
            }, err => {
                alert("Unable to retrieve location.");
            });
        } else {
            alert("Geolocation is not supported by your browser.");
        }
    });

    // Client-Side Autocomplete Logic
    const searchInput = document.getElementById('inputSearchQuery');
    const autocompleteDropdown = document.getElementById('searchAutocompleteDropdown');
    let activeDropdownIndex = -1;

    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        if (query.length < 2) {
            autocompleteDropdown.style.display = 'none';
            return;
        }

        // Filter local ports array (highly performant, sub-millisecond, zero server roundtrips)
        const suggestions = ports.filter(p => 
            p.name.toLowerCase().includes(query) || 
            (p.country_name || '').toLowerCase().includes(query) ||
            (p.un_locode || '').toLowerCase().includes(query)
        ).slice(0, 5);

        if (suggestions.length === 0) {
            autocompleteDropdown.style.display = 'none';
            return;
        }

        let html = '';
        suggestions.forEach((s, idx) => {
            const countryCode = s.country_code ? s.country_code.toLowerCase() : '';
            const flagEmoji = s.country_code 
                ? String.fromCodePoint(...[...s.country_code.toUpperCase()].map(c => 127397 + c.charCodeAt(0)))
                : '🚢';

            html += `
                <div class="autocomplete-item" data-index="${idx}" onclick="selectAutocomplete('${s.name}', ${s.latitude}, ${s.longitude})">
                    <div>
                        <strong>${flagEmoji} ${s.name}</strong>
                        <span class="text-muted d-block small" style="font-size: 0.72rem;">${s.country_name} | ${s.un_locode || 'N/A'}</span>
                    </div>
                    <i class="bi bi-geo-alt-fill text-primary small"></i>
                </div>
            `;
        });

        autocompleteDropdown.innerHTML = html;
        autocompleteDropdown.style.display = 'block';
        activeDropdownIndex = -1;
    });

    // Select Autocomplete item
    window.selectAutocomplete = function(name, lat, lng) {
        searchInput.value = name;
        autocompleteDropdown.style.display = 'none';
        focusMap(lat, lng, name);
        performSearch();
    };

    // Close autocomplete when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !autocompleteDropdown.contains(e.target)) {
            autocompleteDropdown.style.display = 'none';
        }
    });

    // Keyboard navigation in Autocomplete
    searchInput.addEventListener('keydown', function(e) {
        const items = autocompleteDropdown.querySelectorAll('.autocomplete-item');
        if (autocompleteDropdown.style.display === 'block' && items.length > 0) {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeDropdownIndex = (activeDropdownIndex + 1) % items.length;
                setActiveItem(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeDropdownIndex = (activeDropdownIndex - 1 + items.length) % items.length;
                setActiveItem(items);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (activeDropdownIndex > -1) {
                    items[activeDropdownIndex].click();
                }
            }
        }
    });

    function setActiveItem(items) {
        items.forEach(it => it.classList.remove('active'));
        if (activeDropdownIndex > -1) {
            items[activeDropdownIndex].classList.add('active');
            items[activeDropdownIndex].scrollIntoView({ block: 'nearest' });
        }
    }

    // Dynamic Active Filter Chips update
    const filterForm = document.getElementById('ports-filter-form');
    const chipsContainer = document.getElementById('filter-chips-container');

    function renderFilterChips() {
        chipsContainer.innerHTML = '';
        const formData = new FormData(filterForm);
        let chipsHtml = '';

        for (const [key, value] of formData.entries()) {
            if (value !== 'all' && value !== '') {
                let displayName = value;
                
                // For Country ID, resolve country name from select option label
                if (key === 'country_id') {
                    const option = filterForm.querySelector(`select[name="country_id"] option[value="${value}"]`);
                    displayName = option ? option.text : value;
                }

                chipsHtml += `
                    <div class="filter-chip-badge">
                        <span>${displayName}</span>
                        <span class="filter-chip-close" onclick="clearFilterField('${key}')"><i class="bi bi-x"></i></span>
                    </div>
                `;
            }
        }

        if (chipsHtml) {
            chipsContainer.innerHTML = `
                <span class="text-muted small fw-bold me-2" style="font-size: 0.75rem;">Active Filters:</span>
                <div class="d-flex flex-wrap gap-2">${chipsHtml}</div>
            `;
        }
    }

    window.clearFilterField = function(fieldName) {
        const field = filterForm.querySelector(`[name="${fieldName}"]`);
        if (field) {
            if (field.tagName === 'SELECT') {
                field.value = 'all';
            } else {
                field.value = '';
            }
            performSearch();
        }
    };

    // Reset filters panel button
    document.getElementById('btn-reset-filters-panel').addEventListener('click', () => {
        filterForm.reset();
        filterForm.querySelectorAll('select').forEach(sel => sel.value = 'all');
        filterForm.querySelector('input[name="search"]').value = '';
        performSearch();
    });

    // AJAX Form and Search Submission
    let searchTimeout;
    
    function performSearch() {
        renderFilterChips();

        const formData = new FormData(filterForm);
        const params = new URLSearchParams();
        for (const [key, value] of formData.entries()) {
            if (value !== 'all' && value !== '') {
                params.append(key, value);
            }
        }
        
        // Show Skeleton loading state instead of freezing
        const tbody = document.querySelector('table tbody');
        if (tbody) {
            tbody.innerHTML = `
                <tr class="shimmer-skeleton-row"><td colspan="8" class="py-3 bg-light opacity-50">&nbsp;</td></tr>
                <tr class="shimmer-skeleton-row"><td colspan="8" class="py-3 bg-light opacity-50">&nbsp;</td></tr>
                <tr class="shimmer-skeleton-row"><td colspan="8" class="py-3 bg-light opacity-50">&nbsp;</td></tr>
            `;
        }

        fetch(`${window.location.pathname}?${params.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                // Update table body
                if (tbody) tbody.innerHTML = res.html;
                
                // Update pagination container
                const pagContainer = document.querySelector('.card-footer');
                if (pagContainer) {
                    if (res.total > 0) {
                        pagContainer.style.setProperty('display', 'flex', 'important');
                        
                        const infoDiv = pagContainer.querySelector('.pagination-info');
                        if (infoDiv) {
                            infoDiv.innerHTML = `Showing results of ${res.total} ports`;
                        }
                        
                        const linksDiv = pagContainer.querySelector('.pagination-links');
                        if (linksDiv) {
                            linksDiv.innerHTML = res.pagination;
                        }
                    } else {
                        pagContainer.style.setProperty('display', 'none', 'important');
                    }
                }
                
                // Redraw map markers
                drawMarkers(res.mapPorts);
                
                // Update active count badge
                const activeBadge = document.querySelector('.badge.bg-white span');
                if (activeBadge) {
                    activeBadge.innerHTML = `Active Ports Tracked: ${res.mapPorts.length}`;
                }
            }
        })
        .catch(err => {
            console.error('Search error:', err);
        });
    }

    // Debounce search input
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(performSearch, 350);
        });
    }

    // Handle dropdown changes instantly
    filterForm.querySelectorAll('select').forEach(select => {
        select.addEventListener('change', performSearch);
    });

    // Block standard form submit to keep it single-page
    filterForm.addEventListener('submit', (e) => {
        e.preventDefault();
        performSearch();
    });

    // Intercept pagination links to route through AJAX
    document.addEventListener('click', function(e) {
        const pagLink = e.target.closest('.pagination a');
        if (pagLink) {
            e.preventDefault();
            const url = new URL(pagLink.href);
            const page = url.searchParams.get('page');
            
            const formData = new FormData(filterForm);
            const params = new URLSearchParams();
            for (const [key, value] of formData.entries()) {
                if (value !== 'all' && value !== '') {
                    params.append(key, value);
                }
            }
            params.set('page', page);

            const tbody = document.querySelector('table tbody');
            if (tbody) {
                tbody.innerHTML = `
                    <tr class="shimmer-skeleton-row"><td colspan="8" class="py-3 bg-light opacity-50">&nbsp;</td></tr>
                    <tr class="shimmer-skeleton-row"><td colspan="8" class="py-3 bg-light opacity-50">&nbsp;</td></tr>
                `;
            }

            fetch(`${window.location.pathname}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(res => {
                if (res.success) {
                    if (tbody) tbody.innerHTML = res.html;
                    const pagContainer = document.querySelector('.card-footer');
                    if (pagContainer) {
                        if (res.total > 0) {
                            pagContainer.style.setProperty('display', 'flex', 'important');
                            
                            const infoDiv = pagContainer.querySelector('.pagination-info');
                            if (infoDiv) {
                                infoDiv.innerHTML = `Showing results of ${res.total} ports`;
                            }
                            
                            const linksDiv = pagContainer.querySelector('.pagination-links');
                            if (linksDiv) {
                                linksDiv.innerHTML = res.pagination;
                            }
                        } else {
                            pagContainer.style.setProperty('display', 'none', 'important');
                        }
                    }
                }
            })
            .catch(err => {
                console.error('Pagination error:', err);
            });
        }
    });

    // Initial render of chips on load
    renderFilterChips();
</script>
@endpush
