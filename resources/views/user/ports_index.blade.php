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

    /* Timeline Nodes styling */
    .timeline-step-node {
        cursor: pointer;
        transition: transform 0.2s ease, background-color 0.2s ease;
    }
    .timeline-step-node:hover {
        transform: translateY(-2px);
        background-color: #EFF6FF !important;
    }

    /* Animated Polyline dash animation */
    @keyframes leaflet-dash-flow {
        to {
            stroke-dashoffset: -20;
        }
    }
    .animated-polyline {
        stroke-dasharray: 10, 10;
        animation: leaflet-dash-flow 1s linear infinite;
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
            <p class="text-muted mb-0 small">Enterprise maritime infrastructure directory and route risk analysis console</p>
        </div>
        <div>
            <span class="badge bg-white text-dark border border-light-subtle py-2.5 px-3.5 shadow-sm rounded-pill d-inline-flex align-items-center gap-2" style="font-size: 0.85rem;">
                <span class="live-indicator-dot"></span>
                <span class="fw-semibold">Active Ports Tracked: {{ count($mapPorts) }}</span>
            </span>
        </div>
    </div>

    <!-- 4 Statistic Overview Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="metric-card-premium p-4 d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; flex-shrink: 0;">
                    <i class="bi bi-compass fs-4"></i>
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

    <!-- 🚢 Smart Shipping Route Analyzer Card -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 24px; background-color: #FFFFFF;">
        <div class="card-header bg-transparent border-bottom py-3.5 px-4 d-flex align-items-center justify-content-between" style="border-color: #E5E7EB !important;">
            <div class="d-flex align-items-center gap-2.5">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="bi bi-diagram-3-fill fs-5"></i>
                </div>
                <div>
                    <h5 class="fw-bold text-dark mb-0" style="font-size: 1.05rem; font-family: 'Outfit', sans-serif;">🚢 Smart Shipping Route Analyzer</h5>
                    <span class="text-muted small" style="font-size: 0.78rem;">Determine the safest, most efficient international shipping lanes based on live risk & weather intelligence</span>
                </div>
            </div>
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 px-3 py-1.5 rounded-pill small fw-semibold">AI Powered</span>
        </div>
        <div class="card-body p-4">
            <form id="formRouteAnalyzer" class="row g-3">
                @csrf
                <!-- Origin Port -->
                <div class="col-md-6 col-lg-3">
                    <label class="form-label text-dark fw-semibold small mb-1"><i class="bi bi-geo-alt-fill text-danger me-1"></i>Origin Port</label>
                    <select class="form-select bg-light border-light-subtle rounded-3" id="selectOriginPort" name="origin_port_id" required style="font-size: 0.85rem;">
                        <option value="">Select Origin Port...</option>
                        @foreach($allActivePorts as $p)
                            <option value="{{ $p->id }}" {{ $loop->first ? 'selected' : '' }}>{{ $p->name }} ({{ $p->country ? $p->country->name : 'N/A' }}) - {{ $p->port_code ?? $p->un_locode }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Destination Port -->
                <div class="col-md-6 col-lg-3">
                    <label class="form-label text-dark fw-semibold small mb-1"><i class="bi bi-flag-fill text-success me-1"></i>Destination Port</label>
                    <select class="form-select bg-light border-light-subtle rounded-3" id="selectDestinationPort" name="destination_port_id" required style="font-size: 0.85rem;">
                        <option value="">Select Destination Port...</option>
                        @foreach($allActivePorts as $p)
                            <option value="{{ $p->id }}" {{ $loop->count > 1 && $loop->index === 1 ? 'selected' : '' }}>{{ $p->name }} ({{ $p->country ? $p->country->name : 'N/A' }}) - {{ $p->port_code ?? $p->un_locode }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Priority Selector -->
                <div class="col-md-6 col-lg-3">
                    <label class="form-label text-dark fw-semibold small mb-1"><i class="bi bi-sliders me-1 text-primary"></i>Priority Mode</label>
                    <div class="d-flex gap-2">
                        <input type="radio" class="btn-check" name="priority" id="prioritySafest" value="safest" checked>
                        <label class="btn btn-outline-primary btn-sm flex-fill rounded-3 small fw-semibold py-1.5" for="prioritySafest">
                            <i class="bi bi-shield-check me-1"></i>Safest
                        </label>

                        <input type="radio" class="btn-check" name="priority" id="priorityFastest" value="fastest">
                        <label class="btn btn-outline-primary btn-sm flex-fill rounded-3 small fw-semibold py-1.5" for="priorityFastest">
                            <i class="bi bi-lightning-charge me-1"></i>Fastest
                        </label>

                        <input type="radio" class="btn-check" name="priority" id="priorityCheapest" value="cheapest">
                        <label class="btn btn-outline-primary btn-sm flex-fill rounded-3 small fw-semibold py-1.5" for="priorityCheapest">
                            <i class="bi bi-currency-dollar me-1"></i>Cheapest
                        </label>
                    </div>
                </div>

                <!-- Container Type -->
                <div class="col-md-6 col-lg-3">
                    <label class="form-label text-dark fw-semibold small mb-1"><i class="bi bi-box-seam me-1 text-info"></i>Container Type</label>
                    <select class="form-select bg-light border-light-subtle rounded-3" id="selectContainerType" name="container_type" style="font-size: 0.85rem;">
                        <option value="container" selected>Standard Container (FCL/LCL)</option>
                        <option value="general">General Cargo</option>
                        <option value="liquid">Liquid Bulk / Tanker</option>
                        <option value="bulk">Dry Bulk / Carrier</option>
                    </select>
                </div>

                <!-- Submit Button Row -->
                <div class="col-12 d-flex justify-content-end gap-2 mt-3 pt-2 border-top border-light">
                    <button type="button" class="btn btn-outline-secondary px-3 btn-sm rounded-3 fw-semibold" id="btnResetAnalyzer">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Selection
                    </button>
                    <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fw-semibold shadow-sm" id="btnSubmitAnalyzer">
                        <i class="bi bi-play-circle-fill me-1"></i>Analyze Route
                    </button>
                </div>
            </form>
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

    <!-- 📊 Dynamic Route Analysis Results Container (Initially Hidden) -->
    <div id="routeAnalyzerResults" class="mb-4" style="display: none;">
        <!-- Header Banner with Title & Clear Results button -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; background: linear-gradient(135deg, #1E293B, #0F172A); color: #FFFFFF;">
            <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-primary px-2.5 py-1 rounded-pill small fw-semibold" id="resPriorityBadge">Safest Mode</span>
                        <span class="badge bg-info bg-opacity-20 text-info border border-info border-opacity-30 px-2.5 py-1 rounded-pill small fw-semibold" id="resContainerBadge">Standard Container</span>
                    </div>
                    <h4 class="fw-bold text-white mb-0" id="resRouteTitle" style="font-family: 'Outfit', sans-serif;">Belawan → Shanghai</h4>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i>Export PDF / Print
                    </button>
                    <button type="button" class="btn btn-sm btn-danger rounded-pill px-3" id="btnCloseRouteResults">
                        <i class="bi bi-x-lg me-1"></i>Close Results
                    </button>
                </div>
            </div>
        </div>

        <!-- 8 Summary Metric Cards Grid -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4 col-lg-3">
                <div class="metric-card-premium p-3.5">
                    <span class="text-muted small d-block mb-1"><i class="bi bi-signpost-split text-primary me-1"></i>Distance</span>
                    <h4 class="fw-bold text-dark mb-0" id="resDistanceVal">0 NM</h4>
                    <span class="text-muted small" style="font-size: 0.72rem;" id="resDistanceKm">0 km</span>
                </div>
            </div>

            <div class="col-6 col-md-4 col-lg-3">
                <div class="metric-card-premium p-3.5">
                    <span class="text-muted small d-block mb-1"><i class="bi bi-clock-history text-info me-1"></i>Estimated Time</span>
                    <h4 class="fw-bold text-dark mb-0" id="resEtaVal">0 Days</h4>
                    <span class="text-muted small" style="font-size: 0.72rem;">Transit Included</span>
                </div>
            </div>

            <div class="col-6 col-md-4 col-lg-3">
                <div class="metric-card-premium p-3.5">
                    <span class="text-muted small d-block mb-1"><i class="bi bi-shield-exclamation text-warning me-1"></i>Overall Risk Score</span>
                    <h4 class="fw-bold text-dark mb-0" id="resRiskVal">0.0</h4>
                    <span class="badge rounded-pill small fw-semibold px-2 py-0.5" id="resRiskBadge">Low Risk</span>
                </div>
            </div>

            <div class="col-6 col-md-4 col-lg-3">
                <div class="metric-card-premium p-3.5">
                    <span class="text-muted small d-block mb-1"><i class="bi bi-cloud-sun text-success me-1"></i>Current Weather</span>
                    <div class="fw-bold text-dark small text-truncate" id="resWeatherVal">Normal</div>
                    <span class="text-muted small d-block text-truncate" style="font-size: 0.72rem;" id="resWeatherSub">Marine Conditions</span>
                </div>
            </div>

            <div class="col-6 col-md-4 col-lg-3">
                <div class="metric-card-premium p-3.5">
                    <span class="text-muted small d-block mb-1"><i class="bi bi-currency-exchange text-primary me-1"></i>Currency Impact</span>
                    <div class="fw-bold text-dark small text-truncate" id="resCurrencyVal">USD Settlement</div>
                    <span class="text-muted small d-block text-truncate" style="font-size: 0.72rem;">FX Stability</span>
                </div>
            </div>

            <div class="col-6 col-md-4 col-lg-3">
                <div class="metric-card-premium p-3.5">
                    <span class="text-muted small d-block mb-1"><i class="bi bi-bar-chart-steps text-danger me-1"></i>Port Congestion</span>
                    <h4 class="fw-bold text-dark mb-0" id="resCongestionVal">0%</h4>
                    <span class="text-muted small" style="font-size: 0.72rem;">Average Delay</span>
                </div>
            </div>

            <div class="col-6 col-md-4 col-lg-3">
                <div class="metric-card-premium p-3.5">
                    <span class="text-muted small d-block mb-1"><i class="bi bi-globe-americas text-secondary me-1"></i>Geopolitical Status</span>
                    <div class="fw-bold text-dark small text-truncate" id="resGeopoliticalVal">Stable Corridor</div>
                    <span class="text-muted small d-block text-truncate" style="font-size: 0.72rem;">Active News Feeds</span>
                </div>
            </div>

            <div class="col-6 col-md-4 col-lg-3">
                <div class="metric-card-premium p-3.5 bg-primary bg-opacity-10 border-primary border-opacity-20">
                    <span class="text-primary small d-block mb-1 fw-bold"><i class="bi bi-check2-circle me-1"></i>Recommendation</span>
                    <div class="fw-bold text-primary small" id="resRecommendationVal">Optimal & Safe</div>
                </div>
            </div>
        </div>

        <!-- AI Insight Commentary & Interactive Route Timeline Grid -->
        <div class="row g-4 mb-4">
            <!-- Left: AI Insight Commentary Card -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 20px; background-color: #FFFFFF;">
                    <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex align-items-center gap-2" style="border-color: #E5E7EB !important;">
                        <i class="bi bi-robot text-primary fs-5"></i>
                        <h6 class="fw-bold text-dark mb-0" style="font-family: 'Outfit', sans-serif;">AI Route Analysis & Intelligence Advisory</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="p-3 rounded-3 border border-primary border-opacity-15 bg-primary bg-opacity-10 mb-3" style="font-size: 0.88rem; line-height: 1.6; color: #1E293B;" id="resAiInsightText">
                            <!-- Populated dynamically -->
                        </div>
                        <div class="d-flex flex-column gap-2 text-muted small" style="font-size: 0.8rem;">
                            <div class="d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i> Real-time maritime safety parameters evaluated</div>
                            <div class="d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i> Weather anomalies and severe wind speeds checked</div>
                            <div class="d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i> Dynamic port congestion index calculated for all waypoints</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Interactive Transit Nodes Timeline -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 20px; background-color: #FFFFFF;">
                    <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex align-items-center justify-content-between" style="border-color: #E5E7EB !important;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-signpost-split-fill text-success fs-5"></i>
                            <h6 class="fw-bold text-dark mb-0" style="font-family: 'Outfit', sans-serif;">Route Transit Timeline</h6>
                        </div>
                        <span class="text-muted small" style="font-size: 0.72rem;">Click node to focus map</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex flex-column gap-3" id="resTimelineContainer">
                            <!-- Populated dynamically -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alternative Route Comparison Card (Rendered if available) -->
        <div id="resAlternativeContainer" style="display: none;">
            <div class="card border-0 shadow-sm" style="border-radius: 20px; background-color: #FFFFFF; border-left: 5px solid #2563EB !important;">
                <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex align-items-center justify-content-between" style="border-color: #E5E7EB !important;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-shield-shaded text-primary fs-5"></i>
                        <h6 class="fw-bold text-dark mb-0" style="font-family: 'Outfit', sans-serif;">Alternative Route Comparison & Optimization</h6>
                    </div>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 rounded-pill px-3 py-1 small fw-semibold" id="resAltSavingsBadge">Risk Reduction</span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4 align-items-center">
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 border bg-light">
                                <span class="badge bg-secondary mb-2">Original Selected Path</span>
                                <h6 class="fw-bold text-dark mb-1" id="resAltOrigTitle">Belawan → Shanghai</h6>
                                <div class="d-flex gap-3 text-muted small">
                                    <span>Risk Score: <strong class="text-dark" id="resAltOrigRisk">72.0</strong></span>
                                    <span>ETA: <strong class="text-dark" id="resAltOrigEta">11 Days</strong></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 border border-success border-opacity-30 bg-success bg-opacity-10">
                                <span class="badge bg-success mb-2">Recommended Alternative</span>
                                <h6 class="fw-bold text-dark mb-1" id="resAltNewTitle">Belawan → Port Klang → Shanghai</h6>
                                <div class="d-flex gap-3 text-muted small">
                                    <span>Risk Score: <strong class="text-success" id="resAltNewRisk">38.0</strong></span>
                                    <span>ETA: <strong class="text-dark" id="resAltNewEta">13 Days</strong></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="text-muted small mb-0 mt-3" id="resAltRecommendationText">
                        <!-- Populated dynamically -->
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Filter Chips display area -->
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3 px-1" id="filter-chips-container" style="min-height: 38px;">
        <!-- Generated dynamically via JS -->
    </div>

    <!-- Main Sidebar and Grid layout -->
    <div class="row g-4">
        <!-- Sidebar Filter controls -->
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

        <!-- Directory list -->
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

    // Layer group for route polylines and route markers
    const routeLayersGroup = L.layerGroup().addTo(map);

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
                            <div class="d-flex justify-content-between mb-1">
                                <span>UN/LOCODE:</span>
                                <strong class="text-dark">${port.port_code || port.un_locode || 'N/A'}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Country:</span>
                                <strong class="text-dark">${port.country_name || 'N/A'}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Harbor Size / Type:</span>
                                <strong class="text-dark">${port.harbor_size || 'N/A'} / ${port.harbor_type || 'N/A'}</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Coordinates:</span>
                                <strong class="text-dark">${parseFloat(port.latitude).toFixed(4)}, ${parseFloat(port.longitude).toFixed(4)}</strong>
                            </div>
                        </div>
                        <div class="d-flex gap-1.5">
                            <button class="btn btn-sm btn-primary btn-popup-gradient w-100 text-white rounded-pill fw-semibold py-1.5" style="font-size: 0.75rem;" onclick="focusPortInTable(${port.id})">
                                <i class="bi bi-card-text me-1"></i> Highlight in Directory
                            </button>
                        </div>
                    </div>
                `;

                const circleMarker = L.circleMarker([port.latitude, port.longitude], {
                    radius: 7,
                    fillColor: port.risk_score < 40 ? '#10B981' : (port.risk_score < 70 ? '#F59E0B' : '#EF4444'),
                    color: '#FFFFFF',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.9
                }).bindPopup(popupHtml, { className: 'premium-leaflet-popup' });

                markersGroup.addLayer(circleMarker);
                markers[port.id] = circleMarker;
            }
        });

        // Update Telemetry
        const telemetryVisible = document.getElementById('telemetryVisible');
        if (telemetryVisible) {
            telemetryVisible.innerText = data.length;
        }
    }

    drawMarkers(ports);

    // Custom Map Control Buttons handlers
    document.getElementById('mapBtnZoomIn')?.addEventListener('click', () => map.zoomIn());
    document.getElementById('mapBtnZoomOut')?.addEventListener('click', () => map.zoomOut());
    document.getElementById('mapBtnResetCustom')?.addEventListener('click', () => map.setView([20, 0], 2));
    
    document.getElementById('mapBtnFullscreenCustom')?.addEventListener('click', () => {
        const hero = document.querySelector('.map-container-hero');
        if (!document.fullscreenElement) {
            hero.requestFullscreen().catch(err => alert(`Fullscreen error: ${err.message}`));
        } else {
            document.exitFullscreen();
        }
    });

    document.getElementById('mapBtnLocationCustom')?.addEventListener('click', () => {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(pos => {
                map.setView([pos.coords.latitude, pos.coords.longitude], 8);
            }, () => alert('Unable to retrieve location.'));
        }
    });

    // Telemetry zoom tracking
    map.on('zoomend', () => {
        const zoomEl = document.getElementById('telemetryZoom');
        if (zoomEl) zoomEl.innerText = map.getZoom();
    });

    // Function to highlight port row in directory table
    window.focusPortInTable = function(portId) {
        const row = document.getElementById(`port-row-${portId}`);
        if (row) {
            document.querySelectorAll('.table-premium tr').forEach(r => r.classList.remove('active-row'));
            row.classList.add('active-row');
            row.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    };

    // ----------------------------------------------------
    // 🚢 SMART SHIPPING ROUTE ANALYZER LOGIC
    // ----------------------------------------------------
    const formRouteAnalyzer = document.getElementById('formRouteAnalyzer');
    const btnSubmitAnalyzer = document.getElementById('btnSubmitAnalyzer');
    const btnResetAnalyzer = document.getElementById('btnResetAnalyzer');
    const resultsContainer = document.getElementById('routeAnalyzerResults');
    const btnCloseRouteResults = document.getElementById('btnCloseRouteResults');

    btnResetAnalyzer?.addEventListener('click', function() {
        formRouteAnalyzer.reset();
        routeLayersGroup.clearLayers();
        resultsContainer.style.display = 'none';
        map.setView([20, 0], 2);
    });

    btnCloseRouteResults?.addEventListener('click', function() {
        resultsContainer.style.display = 'none';
        routeLayersGroup.clearLayers();
        map.setView([20, 0], 2);
    });

    formRouteAnalyzer?.addEventListener('submit', function(e) {
        e.preventDefault();

        const originId = document.getElementById('selectOriginPort').value;
        const destId = document.getElementById('selectDestinationPort').value;

        if (originId === destId) {
            alert('Pelabuhan tujuan harus berbeda dengan pelabuhan asal.');
            return;
        }

        const originalBtnHtml = btnSubmitAnalyzer.innerHTML;
        btnSubmitAnalyzer.disabled = true;
        btnSubmitAnalyzer.innerHTML = `<span class="spinner-border spinner-border-sm me-1" role="status"></span> Analyzing Route...`;

        const formData = new FormData(formRouteAnalyzer);

        fetch("{{ route('ports.analyze-route') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(res => {
            btnSubmitAnalyzer.disabled = false;
            btnSubmitAnalyzer.innerHTML = originalBtnHtml;

            if (res.success && res.data) {
                renderRouteResults(res.data);
            } else {
                alert(res.message || 'Data belum tersedia.');
            }
        })
        .catch(err => {
            btnSubmitAnalyzer.disabled = false;
            btnSubmitAnalyzer.innerHTML = originalBtnHtml;
            console.error('Route analysis error:', err);
            alert('Terjadi kesalahan saat menghubungkan ke server analisis rute.');
        });
    });

    function renderRouteResults(data) {
        // Show results container
        resultsContainer.style.display = 'block';
        resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });

        // Update Title & Badges
        document.getElementById('resRouteTitle').innerText = `${data.origin.name} → ${data.destination.name}`;
        
        const priorityInput = document.querySelector('input[name="priority"]:checked');
        const priorityLabel = priorityInput ? priorityInput.nextElementSibling.innerText.trim() : 'Safest Mode';
        document.getElementById('resPriorityBadge').innerText = priorityLabel;

        const containerSelect = document.getElementById('selectContainerType');
        const containerLabel = containerSelect ? containerSelect.options[containerSelect.selectedIndex].text : 'Standard Container';
        document.getElementById('resContainerBadge').innerText = containerLabel;

        // Update Summary metrics
        document.getElementById('resDistanceVal').innerText = `${data.summary.distance_nm} NM`;
        document.getElementById('resDistanceKm').innerText = `(${data.summary.distance_km} km)`;
        document.getElementById('resEtaVal').innerText = `${data.summary.eta_days} Days`;
        document.getElementById('resRiskVal').innerText = `${data.summary.risk_score} / 100`;

        const riskBadge = document.getElementById('resRiskBadge');
        riskBadge.innerText = `${data.summary.risk_level} Risk`;
        riskBadge.className = data.summary.risk_level === 'Low' ? 'badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 rounded-pill small fw-semibold px-2 py-0.5' :
                              (data.summary.risk_level === 'Medium' ? 'badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-20 rounded-pill small fw-semibold px-2 py-0.5' :
                              'badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20 rounded-pill small fw-semibold px-2 py-0.5');

        document.getElementById('resWeatherVal').innerText = data.summary.weather_summary;
        document.getElementById('resCurrencyVal').innerText = data.summary.currency_impact;
        document.getElementById('resCongestionVal').innerText = data.summary.port_congestion;
        document.getElementById('resGeopoliticalVal').innerText = data.summary.geopolitical_status;
        document.getElementById('resRecommendationVal').innerText = data.summary.recommendation;

        // Update AI Insight commentary
        document.getElementById('resAiInsightText').innerHTML = `
            <div class="d-flex align-items-start gap-2">
                <i class="bi bi-quote text-primary fs-3 leading-none me-1 opacity-50"></i>
                <div>${data.ai_insight}</div>
            </div>
        `;

        // Update Timeline
        const timelineContainer = document.getElementById('resTimelineContainer');
        let timelineHtml = '';

        data.timeline.forEach((step, idx) => {
            const stepIcon = step.type === 'Origin' ? 'bi-geo-alt-fill text-danger' : (step.type === 'Destination' ? 'bi-flag-fill text-success' : 'bi-diagram-3-fill text-primary');
            const stepBadgeClass = step.type === 'Origin' ? 'bg-danger' : (step.type === 'Destination' ? 'bg-success' : 'bg-primary');

            timelineHtml += `
                <div class="p-3 rounded-3 border border-light bg-light timeline-step-node d-flex align-items-center justify-content-between" onclick="focusRouteNode(${step.latitude}, ${step.longitude}, ${step.port_id})">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-white shadow-sm d-flex align-items-center justify-content-center fw-bold text-dark fs-6" style="width: 38px; height: 38px; flex-shrink: 0;">
                            <i class="bi ${stepIcon}"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge ${stepBadgeClass} bg-opacity-10 text-dark small fw-bold px-2 py-0.5 rounded">${step.type}</span>
                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.9rem;">${step.port_name}</h6>
                            </div>
                            <span class="text-muted small" style="font-size: 0.76rem;">${step.country_name} • Congestion: ${step.congestion}</span>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge ${step.risk_level === 'Low' ? 'bg-success' : (step.risk_level === 'Medium' ? 'bg-warning' : 'bg-danger')} bg-opacity-10 text-dark small fw-bold px-2 py-1 rounded-pill" style="font-size: 0.7rem;">
                            Risk: ${step.risk_score}
                        </span>
                    </div>
                </div>
            `;
            if (idx < data.timeline.length - 1) {
                timelineHtml += `
                    <div class="d-flex justify-content-center my-n1">
                        <i class="bi bi-arrow-down text-muted fs-6 opacity-50"></i>
                    </div>
                `;
            }
        });
        timelineContainer.innerHTML = timelineHtml;

        // Render Alternative Route if present
        const altContainer = document.getElementById('resAlternativeContainer');
        if (data.alternative_route) {
            altContainer.style.display = 'block';
            const alt = data.alternative_route;
            document.getElementById('resAltOrigTitle').innerText = alt.original.route_summary;
            document.getElementById('resAltOrigRisk').innerText = alt.original.risk_score;
            document.getElementById('resAltOrigEta').innerText = `${alt.original.eta_days} Days`;

            document.getElementById('resAltNewTitle').innerText = alt.alternative.route_summary;
            document.getElementById('resAltNewRisk').innerText = alt.alternative.risk_score;
            document.getElementById('resAltNewEta').innerText = `${alt.alternative.eta_days} Days`;
            document.getElementById('resAltSavingsBadge').innerText = `-${alt.alternative.savings_risk_percent}% Risk Savings`;
            document.getElementById('resAltRecommendationText').innerText = alt.alternative.recommendation_text;
        } else {
            altContainer.style.display = 'none';
        }

        // Draw Map Polyline Route & Waypoints
        drawRouteOnMap(data);
    }

    function drawRouteOnMap(data) {
        routeLayersGroup.clearLayers();

        const latLngs = [];
        const routeColor = data.summary.risk_score < 40 ? '#10B981' : (data.summary.risk_score < 65 ? '#F59E0B' : '#EF4444');

        data.timeline.forEach(node => {
            const latLng = [node.latitude, node.longitude];
            latLngs.push(latLng);

            const iconHtml = node.type === 'Origin' 
                ? `<div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center shadow-lg border border-white" style="width: 28px; height: 28px; font-weight: 700; font-size: 14px;"><i class="bi bi-geo-alt-fill"></i></div>`
                : (node.type === 'Destination' 
                    ? `<div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center shadow-lg border border-white" style="width: 28px; height: 28px; font-weight: 700; font-size: 14px;"><i class="bi bi-flag-fill"></i></div>`
                    : `<div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-lg border border-white" style="width: 24px; height: 24px; font-weight: 700; font-size: 12px;"><i class="bi bi-diagram-3-fill"></i></div>`);

            const customIcon = L.divIcon({
                html: iconHtml,
                className: 'custom-route-marker-icon',
                iconSize: [28, 28],
                iconAnchor: [14, 14]
            });

            const popupHtml = `
                <div class="p-2 text-dark" style="font-family: 'Outfit', sans-serif; min-width: 240px;">
                    <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                        <span class="badge ${node.type === 'Origin' ? 'bg-danger' : (node.type === 'Destination' ? 'bg-success' : 'bg-primary')} px-2 py-0.5 rounded">${node.type}</span>
                        <span class="fw-bold small text-dark">${node.port_name}</span>
                    </div>
                    <div class="text-muted small" style="font-size: 0.78rem; line-height: 1.6;">
                        <div>Country: <strong class="text-dark">${node.country_name}</strong></div>
                        <div>Status: <strong class="text-success">${node.status}</strong></div>
                        <div>Congestion Index: <strong class="text-dark">${node.congestion}</strong></div>
                        <div>Weather: <strong class="text-dark">${node.weather}</strong></div>
                        <div>Risk Score: <strong class="text-dark">${node.risk_score} (${node.risk_level})</strong></div>
                    </div>
                </div>
            `;

            const marker = L.marker(latLng, { icon: customIcon }).bindPopup(popupHtml, { className: 'premium-leaflet-popup' });
            routeLayersGroup.addLayer(marker);
        });

        // Add Animated Polyline
        const polyline = L.polyline(latLngs, {
            color: routeColor,
            weight: 5,
            opacity: 0.85,
            className: 'animated-polyline'
        }).addTo(routeLayersGroup);

        // Fit map bounds to encompass the full route smoothly
        if (latLngs.length > 0) {
            map.fitBounds(polyline.getBounds(), { padding: [60, 60] });
        }
    }

    window.focusRouteNode = function(lat, lng, portId) {
        map.setView([lat, lng], 8, { animate: true });
        routeLayersGroup.eachLayer(layer => {
            if (layer.getLatLng && Math.abs(layer.getLatLng().lat - lat) < 0.0001 && Math.abs(layer.getLatLng().lng - lng) < 0.0001) {
                layer.openPopup();
            }
        });
    };

    // Filter Ajax Handlers
    const filterForm = document.getElementById('ports-filter-form');
    const searchInput = document.getElementById('inputSearchQuery');
    let searchTimeout = null;

    function renderFilterChips() {
        const chipsContainer = document.getElementById('filter-chips-container');
        if (!chipsContainer) return;
        
        chipsContainer.innerHTML = '';
        const formData = new FormData(filterForm);
        let activeCount = 0;

        for (const [key, value] of formData.entries()) {
            if (value && value !== 'all' && value !== '') {
                activeCount++;
                let labelText = `${key}: ${value}`;
                
                if (key === 'search') labelText = `Search: "${value}"`;
                if (key === 'region') labelText = `Region: ${value}`;
                if (key === 'country_id') {
                    const option = filterForm.querySelector(`select[name="country_id"] option[value="${value}"]`);
                    labelText = `Country: ${option ? option.text : value}`;
                }
                if (key === 'harbor_size') labelText = `Size: ${value}`;
                if (key === 'harbor_type') labelText = `Type: ${value}`;
                if (key === 'status') labelText = `Status: ${value}`;

                const chip = document.createElement('span');
                chip.className = 'filter-chip-badge';
                chip.innerHTML = `
                    ${labelText}
                    <span class="filter-chip-close" onclick="removeSingleFilter('${key}')">&times;</span>
                `;
                chipsContainer.appendChild(chip);
            }
        }

        if (activeCount > 0) {
            const clearAllBtn = document.createElement('button');
            clearAllBtn.type = 'button';
            clearAllBtn.className = 'btn btn-link text-danger text-decoration-none p-0 ms-2 small fw-semibold';
            clearAllBtn.style.fontSize = '0.78rem';
            clearAllBtn.innerHTML = '<i class="bi bi-trash me-1"></i>Clear All';
            clearAllBtn.onclick = resetAllFilters;
            chipsContainer.appendChild(clearAllBtn);
        }
    }

    window.removeSingleFilter = function(key) {
        const field = filterForm.querySelector(`[name="${key}"]`);
        if (field) {
            if (field.tagName === 'SELECT') {
                field.value = 'all';
            } else {
                field.value = '';
            }
            performSearch();
        }
    };

    function resetAllFilters() {
        filterForm.reset();
        filterForm.querySelectorAll('select').forEach(s => s.value = 'all');
        filterForm.querySelectorAll('input[type="text"]').forEach(i => i.value = '');
        performSearch();
    }

    document.getElementById('btn-reset-filters-panel')?.addEventListener('click', resetAllFilters);

    function performSearch() {
        renderFilterChips();

        const formData = new FormData(filterForm);
        const params = new URLSearchParams();
        for (const [key, value] of formData.entries()) {
            if (value !== 'all' && value !== '') {
                params.append(key, value);
            }
        }

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
                        if (infoDiv) infoDiv.innerHTML = `Showing results of ${res.total} ports`;
                        const linksDiv = pagContainer.querySelector('.pagination-links');
                        if (linksDiv) linksDiv.innerHTML = res.pagination;
                    } else {
                        pagContainer.style.setProperty('display', 'none', 'important');
                    }
                }
                
                drawMarkers(res.mapPorts);
                const activeBadge = document.querySelector('.badge.bg-white span');
                if (activeBadge) activeBadge.innerHTML = `Active Ports Tracked: ${res.mapPorts.length}`;
            }
        })
        .catch(err => console.error('Search error:', err));
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

    // Initial render of chips on load
    renderFilterChips();
</script>
@endpush
