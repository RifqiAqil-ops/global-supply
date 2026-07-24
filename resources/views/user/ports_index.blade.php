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
        border-radius: 20px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.01);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .metric-card-premium:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 18px -6px rgba(0, 0, 0, 0.06);
    }

    /* Leaflet Map Hero & Glassmorphism Panels */
    .map-container-hero {
        position: relative;
        height: 540px;
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
        background: rgba(255, 255, 255, 0.88);
        backdrop-filter: blur(16px) saturate(180%);
        -webkit-backdrop-filter: blur(16px) saturate(180%);
        border: 1px solid rgba(255, 255, 255, 0.6);
        border-radius: 20px;
        padding: 14px 16px;
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

    /* Searchable Combobox Styling */
    .combobox-wrapper {
        position: relative;
    }
    .combobox-input {
        background-color: #F8FAFC !important;
        border: 1px solid #E2E8F0 !important;
        border-radius: 14px !important;
        font-size: 0.85rem !important;
        padding: 10px 14px !important;
        transition: all 0.2s ease !important;
    }
    .combobox-input:focus {
        background-color: #FFFFFF !important;
        border-color: #2563EB !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1) !important;
    }
    .combobox-dropdown {
        position: absolute;
        top: 105%;
        left: 0;
        right: 0;
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        box-shadow: 0 12px 30px -5px rgba(0, 0, 0, 0.1);
        z-index: 10000;
        max-height: 260px;
        overflow-y: auto;
        display: none;
    }
    .combobox-item {
        padding: 10px 14px;
        cursor: pointer;
        font-size: 0.83rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #F1F5F9;
        transition: background 0.15s ease;
    }
    .combobox-item:last-child {
        border-bottom: none;
    }
    .combobox-item:hover, .combobox-item.active {
        background-color: #EFF6FF;
    }
    .combobox-item .port-name {
        font-weight: 600;
        color: #0F172A;
    }
    .combobox-item .port-meta {
        font-size: 0.75rem;
        color: #64748B;
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
    .table-premium tbody tr:hover {
        background-color: #EFF6FF !important;
        cursor: pointer;
    }
    .table-premium tr.active-row {
        background-color: #DBEAFE !important;
        font-weight: 500;
    }

    /* Refined Step-by-Step Vertical Timeline */
    .timeline-vertical-wrapper {
        position: relative;
        padding-left: 28px;
    }
    .timeline-vertical-line {
        position: absolute;
        left: 13px;
        top: 18px;
        bottom: 18px;
        width: 3px;
        background: linear-gradient(180deg, #EF4444, #3B82F6 50%, #10B981 100%);
        border-radius: 2px;
    }
    .timeline-step-card {
        position: relative;
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        padding: 14px 16px;
        margin-bottom: 16px;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .timeline-step-card:last-child {
        margin-bottom: 0;
    }
    .timeline-step-card:hover {
        transform: translateX(4px);
        border-color: #2563EB;
        box-shadow: 0 6px 16px -4px rgba(37, 99, 235, 0.12);
        background: #F8FAFC;
    }
    .timeline-step-dot {
        position: absolute;
        left: -29px;
        top: 16px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        border: 3px solid #FFFFFF;
        box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.1);
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

    /* Categorized Section Cards */
    .info-group-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 20px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.01);
    }
    .info-group-header {
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.6px;
        text-transform: uppercase;
        color: #64748B;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
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
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 px-3 py-1.5 rounded-pill small fw-semibold">Intelligence Active</span>
        </div>
        <div class="card-body p-4">
            <form id="formRouteAnalyzer" class="row g-3">
                @csrf
                <!-- Default hidden field for container_type to satisfy backend validation without breaking contract -->
                <input type="hidden" name="container_type" value="container">

                <!-- 1. Origin Port Searchable Combobox -->
                <div class="col-md-5">
                    <label class="form-label text-dark fw-semibold small mb-1"><i class="bi bi-geo-alt-fill text-danger me-1"></i>Origin Port</label>
                    <div class="combobox-wrapper">
                        <input type="hidden" id="selectOriginPort" name="origin_port_id" value="" required>
                        <input type="text" class="form-control combobox-input" id="inputOriginPortSearch" placeholder="Search origin port..." autocomplete="off" value="">
                        <div class="combobox-dropdown" id="dropdownOriginPort"></div>
                    </div>
                </div>

                <!-- 2. Destination Port Searchable Combobox -->
                <div class="col-md-5">
                    <label class="form-label text-dark fw-semibold small mb-1"><i class="bi bi-flag-fill text-success me-1"></i>Destination Port</label>
                    <div class="combobox-wrapper">
                        <input type="hidden" id="selectDestinationPort" name="destination_port_id" value="" required>
                        <input type="text" class="form-control combobox-input" id="inputDestinationPortSearch" placeholder="Search destination port..." autocomplete="off" value="">
                        <div class="combobox-dropdown" id="dropdownDestinationPort"></div>
                    </div>
                </div>

                <!-- 3. Priority Selector -->
                <div class="col-md-2">
                    <label class="form-label text-dark fw-semibold small mb-1"><i class="bi bi-sliders me-1 text-primary"></i>Priority Mode</label>
                    <div class="d-flex gap-1.5">
                        <input type="radio" class="btn-check" name="priority" id="prioritySafest" value="safest" checked>
                        <label class="btn btn-outline-primary btn-sm flex-fill rounded-3 small fw-semibold py-2" for="prioritySafest" title="Safest Route">
                            <i class="bi bi-shield-check"></i> Safest
                        </label>

                        <input type="radio" class="btn-check" name="priority" id="priorityFastest" value="fastest">
                        <label class="btn btn-outline-primary btn-sm flex-fill rounded-3 small fw-semibold py-2" for="priorityFastest" title="Fastest Route">
                            <i class="bi bi-lightning-charge"></i>
                        </label>

                        <input type="radio" class="btn-check" name="priority" id="priorityCheapest" value="cheapest">
                        <label class="btn btn-outline-primary btn-sm flex-fill rounded-3 small fw-semibold py-2" for="priorityCheapest" title="Cheapest Route">
                            <i class="bi bi-currency-dollar"></i>
                        </label>
                    </div>
                </div>

                <!-- Submit Button Row -->
                <div class="col-12 d-flex justify-content-end gap-2 mt-3 pt-2 border-top border-light">
                    <button type="button" class="btn btn-outline-secondary px-3 btn-sm rounded-3 fw-semibold" id="btnResetAnalyzer">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Selection
                    </button>
                    <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fw-semibold shadow-sm" id="btnSubmitAnalyzer" disabled>
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
        <!-- 1. DENSE LIGHT-MODE RESULT HEADER BAR -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; background-color: #FFFFFF; border: 1px solid #E5E7EB !important;">
            <div class="card-body p-3.5 px-4">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    <!-- Left: Corridor & Route Badges -->
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center shadow-sm" style="width: 42px; height: 42px; flex-shrink: 0;">
                            <i class="bi bi-compass-fill fs-5 text-primary"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-0.5">
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 px-2.5 py-0.5 rounded-pill small fw-semibold" id="resPriorityBadge">Safest Mode</span>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 px-2.5 py-0.5 rounded-pill small fw-semibold" id="resHeaderRiskBadge">Low Risk</span>
                            </div>
                            <h4 class="fw-bold text-dark mb-0" id="resRouteTitle" style="font-family: 'Outfit', sans-serif; letter-spacing: -0.3px; font-size: 1.2rem;">Belawan → Shanghai</h4>
                        </div>
                    </div>

                    <!-- Center: Quick Horizontal Telemetry Specs -->
                    <div class="d-flex align-items-center gap-4 py-2 py-lg-0 border-top border-bottom border-lg-0 border-light px-2 px-lg-0">
                        <div>
                            <span class="text-muted d-block text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.5px;">Distance</span>
                            <span class="fw-bold text-dark" id="resHeaderDistance">0 NM <small class="text-muted font-normal" style="font-size: 0.75rem;">(0 km)</small></span>
                        </div>
                        <div class="vr bg-secondary opacity-25 d-none d-sm-block" style="height: 24px;"></div>
                        <div>
                            <span class="text-muted d-block text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.5px;">ETA</span>
                            <span class="fw-bold text-dark" id="resHeaderEta">0 Days</span>
                        </div>
                        <div class="vr bg-secondary opacity-25 d-none d-sm-block" style="height: 24px;"></div>
                        <div>
                            <span class="text-muted d-block text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.5px;">Overall Risk</span>
                            <span class="fw-bold text-warning" id="resHeaderRiskScore">0.0 / 100</span>
                        </div>
                    </div>

                    <!-- Right: Action Button -->
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3.5 py-1.5 fw-semibold" id="btnCloseRouteResults">
                            <i class="bi bi-x-lg me-1"></i>Close Result
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. CATEGORIZED INFORMATION GROUPS (4 Semantic Panels) -->
        <div class="row g-4 mb-4">
            <!-- Group 1: Transport Telemetry -->
            <div class="col-md-6 col-lg-3">
                <div class="info-group-card h-100">
                    <div class="info-group-header">
                        <i class="bi bi-speedometer2 text-primary fs-6"></i>Transport Specs
                    </div>
                    <div class="mb-3">
                        <span class="text-muted small d-block mb-1">Total Distance</span>
                        <div class="d-flex align-items-baseline gap-1">
                            <h3 class="fw-bold text-dark mb-0" id="resDistanceVal">0 NM</h3>
                            <span class="text-muted small" id="resDistanceKm">(0 km)</span>
                        </div>
                    </div>
                    <hr class="my-2 border-light">
                    <div>
                        <span class="text-muted small d-block mb-1">Estimated Travel Time</span>
                        <h4 class="fw-bold text-primary mb-0" id="resEtaVal">0 Days</h4>
                        <span class="text-muted" style="font-size: 0.72rem;">Transit & Buffer Included</span>
                    </div>
                </div>
            </div>

            <!-- Group 2: Risk & Congestion -->
            <div class="col-md-6 col-lg-3">
                <div class="info-group-card h-100">
                    <div class="info-group-header">
                        <i class="bi bi-shield-lock text-warning fs-6"></i>Risk & Port Congestion
                    </div>
                    <div class="mb-3">
                        <span class="text-muted small d-block mb-1">Composite Risk Score</span>
                        <div class="d-flex align-items-center gap-2">
                            <h3 class="fw-bold text-dark mb-0" id="resRiskVal">0.0</h3>
                            <span class="badge rounded-pill small fw-semibold px-2.5 py-1" id="resRiskBadge">Low Risk</span>
                        </div>
                    </div>
                    <hr class="my-2 border-light">
                    <div>
                        <span class="text-muted small d-block mb-1">Waypoints Congestion</span>
                        <h4 class="fw-bold text-danger mb-0" id="resCongestionVal">0%</h4>
                        <span class="text-muted" style="font-size: 0.72rem;">Average Delay Index</span>
                    </div>
                </div>
            </div>

            <!-- Group 3: Environment & Macro Factors -->
            <div class="col-md-6 col-lg-3">
                <div class="info-group-card h-100">
                    <div class="info-group-header">
                        <i class="bi bi-globe-americas text-success fs-6"></i>Environment & Macro
                    </div>
                    <div class="mb-2">
                        <span class="text-muted small d-block">Current Weather</span>
                        <strong class="text-dark small d-block text-truncate" id="resWeatherVal">Normal</strong>
                        <span class="text-muted" style="font-size: 0.72rem;" id="resWeatherSub">Marine Conditions</span>
                    </div>
                    <div class="mb-2">
                        <span class="text-muted small d-block">FX Currency Impact</span>
                        <strong class="text-dark small d-block text-truncate" id="resCurrencyVal">USD Settlement</strong>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Geopolitical Corridor</span>
                        <strong class="text-dark small d-block text-truncate" id="resGeopoliticalVal">Stable Corridor</strong>
                    </div>
                </div>
            </div>

            <!-- Group 4: Route Recommendation Panel -->
            <div class="col-md-6 col-lg-3">
                <div class="info-group-card h-100 bg-primary bg-opacity-10 border-primary border-opacity-25">
                    <div class="info-group-header text-primary">
                        <i class="bi bi-check2-circle fs-6"></i>Route Recommendation
                    </div>
                    <div class="p-3 rounded-3 bg-white border border-primary border-opacity-20 shadow-sm">
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 rounded-pill mb-2 px-2.5 py-1 small fw-semibold">Evaluated</span>
                        <div class="fw-bold text-dark fs-6" id="resRecommendationVal">Optimal & Safe</div>
                        <p class="text-muted small mb-0 mt-1" style="font-size: 0.76rem;">Standard Maritime Dispatch Recommended</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. ROUTE ADVISORY & REFINED STEP-BY-STEP TIMELINE -->
        <div class="row g-4 mb-4">
            <!-- Left: Route Advisory & Intelligence Advisory -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 20px; background-color: #FFFFFF;">
                    <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex align-items-center gap-2" style="border-color: #E5E7EB !important;">
                        <i class="bi bi-shield-check text-primary fs-5"></i>
                        <h6 class="fw-bold text-dark mb-0" style="font-family: 'Outfit', sans-serif;">Route Advisory & Strategic Intelligence</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="p-3.5 rounded-3 border border-primary border-opacity-20 bg-primary bg-opacity-10 mb-3" style="font-size: 0.88rem; line-height: 1.6; color: #1E293B;" id="resAiInsightText">
                            <!-- Populated dynamically -->
                        </div>
                        <div class="d-flex flex-column gap-2.5 text-muted small mt-3" style="font-size: 0.8rem;">
                            <div class="d-flex align-items-center gap-2"><i class="bi bi-shield-check text-success fs-6"></i> Real-time maritime safety & piracy parameters evaluated</div>
                            <div class="d-flex align-items-center gap-2"><i class="bi bi-cloud-sun text-info fs-6"></i> Weather anomalies and severe wind speeds checked</div>
                            <div class="d-flex align-items-center gap-2"><i class="bi bi-bar-chart-steps text-warning fs-6"></i> Dynamic port congestion index calculated for all waypoints</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Interactive Step-by-Step Vertical Timeline -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 20px; background-color: #FFFFFF;">
                    <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex align-items-center justify-content-between" style="border-color: #E5E7EB !important;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-diagram-3 text-success fs-5"></i>
                            <h6 class="fw-bold text-dark mb-0" style="font-family: 'Outfit', sans-serif;">Route Transit Timeline</h6>
                        </div>
                        <span class="text-muted small" style="font-size: 0.72rem;">Click step to center map</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="timeline-vertical-wrapper" id="resTimelineContainer">
                            <div class="timeline-vertical-line"></div>
                            <!-- Populated dynamically -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. REFINED ROUTE COMPARISON CARD -->
        <div id="resAlternativeContainer" style="display: none;">
            <div class="card border-0 shadow-sm" style="border-radius: 20px; background-color: #FFFFFF; border-left: 6px solid #2563EB !important;">
                <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex align-items-center justify-content-between" style="border-color: #E5E7EB !important;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-shield-shaded text-primary fs-5"></i>
                        <h6 class="fw-bold text-dark mb-0" style="font-family: 'Outfit', sans-serif;">Alternative Route Optimization & Comparison</h6>
                    </div>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1.5 small fw-semibold" id="resAltSavingsBadge">Risk Reduction</span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4 align-items-center">
                        <!-- Left: Original Path -->
                        <div class="col-md-6">
                            <div class="p-3.5 rounded-3 border bg-light">
                                <span class="badge bg-secondary mb-2 px-2.5 py-1">Original Selected Path</span>
                                <h6 class="fw-bold text-dark mb-2" id="resAltOrigTitle" style="font-size: 0.95rem;">Belawan → Shanghai</h6>
                                <div class="d-flex gap-4 text-muted small">
                                    <div>Risk Score: <strong class="text-dark" id="resAltOrigRisk">72.0</strong></div>
                                    <div>ETA: <strong class="text-dark" id="resAltOrigEta">11 Days</strong></div>
                                </div>
                            </div>
                        </div>
                        <!-- Right: Optimized Alternative Path -->
                        <div class="col-md-6">
                            <div class="p-3.5 rounded-3 border border-success border-opacity-40 bg-success bg-opacity-10">
                                <span class="badge bg-success mb-2 px-2.5 py-1"><i class="bi bi-star-fill me-1"></i>Recommended Alternative</span>
                                <h6 class="fw-bold text-dark mb-2" id="resAltNewTitle" style="font-size: 0.95rem;">Belawan → Port Klang → Shanghai</h6>
                                <div class="d-flex gap-4 text-muted small">
                                    <div>Risk Score: <strong class="text-success fw-bold" id="resAltNewRisk">38.0</strong></div>
                                    <div>ETA: <strong class="text-dark" id="resAltNewEta">13 Days</strong></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="text-muted small mb-0 mt-3.5 pt-2 border-top border-light" id="resAltRecommendationText">
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
    const allActivePortsData = @json($allActivePorts);

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

    map.on('zoomend', () => {
        const zoomEl = document.getElementById('telemetryZoom');
        if (zoomEl) zoomEl.innerText = map.getZoom();
    });

    window.focusPortInTable = function(portId) {
        const row = document.getElementById(`port-row-${portId}`);
        if (row) {
            document.querySelectorAll('.table-premium tr').forEach(r => r.classList.remove('active-row'));
            row.classList.add('active-row');
            row.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    };

    // ----------------------------------------------------
    // 🔍 SEARCHABLE COMBOBOX CONTROLS (GitHub/Notion Style)
    // ----------------------------------------------------
    function updateAnalyzerSubmitButtonState() {
        const originVal = document.getElementById('selectOriginPort')?.value;
        const destVal = document.getElementById('selectDestinationPort')?.value;
        const btnSubmit = document.getElementById('btnSubmitAnalyzer');
        if (btnSubmit) {
            if (originVal && destVal && originVal !== destVal) {
                btnSubmit.disabled = false;
            } else {
                btnSubmit.disabled = true;
            }
        }
    }

    function initCombobox(inputId, hiddenId, dropdownId) {
        const input = document.getElementById(inputId);
        const hidden = document.getElementById(hiddenId);
        const dropdown = document.getElementById(dropdownId);
        if (!input || !hidden || !dropdown) return;

        function renderOptions(filterText = '') {
            dropdown.innerHTML = '';
            const query = filterText.toLowerCase().trim();
            
            const filtered = allActivePortsData.filter(port => {
                const pName = (port.name || '').toLowerCase();
                const cName = (port.country ? port.country.name : '').toLowerCase();
                const pCode = (port.port_code || port.un_locode || '').toLowerCase();
                return pName.includes(query) || cName.includes(query) || pCode.includes(query);
            });

            if (filtered.length === 0) {
                dropdown.innerHTML = `<div class="p-3 text-muted small text-center">No ports found matching "${filterText}"</div>`;
                return;
            }

            filtered.slice(0, 30).forEach(port => {
                const cName = port.country ? port.country.name : 'N/A';
                const code = port.port_code || port.un_locode || 'N/A';
                const label = `${port.name} (${cName}) - ${code}`;
                
                const item = document.createElement('div');
                item.className = 'combobox-item';
                if (hidden.value == port.id) item.classList.add('active');
                item.innerHTML = `
                    <div>
                        <div class="port-name">${port.name}</div>
                        <div class="port-meta">${cName} • Code: ${code}</div>
                    </div>
                    <i class="bi bi-chevron-right text-muted small"></i>
                `;
                item.onclick = function(e) {
                    e.stopPropagation();
                    hidden.value = port.id;
                    input.value = label;
                    dropdown.style.display = 'none';
                    updateAnalyzerSubmitButtonState();
                };
                dropdown.appendChild(item);
            });
        }

        input.addEventListener('focus', function() {
            renderOptions(input.value);
            dropdown.style.display = 'block';
        });

        input.addEventListener('input', function() {
            if (input.value.trim() === '') {
                hidden.value = '';
                updateAnalyzerSubmitButtonState();
            }
            renderOptions(input.value);
            dropdown.style.display = 'block';
        });

        document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });
    }

    initCombobox('inputOriginPortSearch', 'selectOriginPort', 'dropdownOriginPort');
    initCombobox('inputDestinationPortSearch', 'selectDestinationPort', 'dropdownDestinationPort');
    updateAnalyzerSubmitButtonState();

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
        document.getElementById('selectOriginPort').value = '';
        document.getElementById('inputOriginPortSearch').value = '';
        document.getElementById('selectDestinationPort').value = '';
        document.getElementById('inputDestinationPortSearch').value = '';

        if (vesselAnimationInterval) {
            clearInterval(vesselAnimationInterval);
            vesselAnimationInterval = null;
        }

        routeLayersGroup.clearLayers();
        resultsContainer.style.display = 'none';
        map.setView([20, 0], 2);
        updateAnalyzerSubmitButtonState();
    });

    btnCloseRouteResults?.addEventListener('click', function() {
        if (vesselAnimationInterval) {
            clearInterval(vesselAnimationInterval);
            vesselAnimationInterval = null;
        }
        resultsContainer.style.display = 'none';
        routeLayersGroup.clearLayers();
        map.setView([20, 0], 2);
    });

    formRouteAnalyzer?.addEventListener('submit', function(e) {
        e.preventDefault();

        const originId = document.getElementById('selectOriginPort').value;
        const destId = document.getElementById('selectDestinationPort').value;

        if (!originId || !destId) {
            alert('Silakan pilih pelabuhan asal dan tujuan terlebih dahulu.');
            return;
        }

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
        resultsContainer.style.display = 'block';

        // 1. Update Dense Light Header Bar
        document.getElementById('resRouteTitle').innerText = `${data.origin.name} → ${data.destination.name}`;
        
        const priorityInput = document.querySelector('input[name="priority"]:checked');
        const priorityLabel = priorityInput ? priorityInput.nextElementSibling.innerText.trim() : 'Safest Mode';
        document.getElementById('resPriorityBadge').innerText = priorityLabel;

        const headerRiskBadge = document.getElementById('resHeaderRiskBadge');
        headerRiskBadge.innerText = `${data.summary.risk_level} Risk`;
        headerRiskBadge.className = data.summary.risk_level === 'Low' ? 'badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 px-2.5 py-0.5 rounded-pill small fw-semibold' :
                              (data.summary.risk_level === 'Medium' ? 'badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-20 px-2.5 py-0.5 rounded-pill small fw-semibold' :
                              'badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20 px-2.5 py-0.5 rounded-pill small fw-semibold');

        const formattedDistNm = parseFloat(data.summary.distance_nm).toFixed(1);
        const formattedDistKm = parseFloat(data.summary.distance_km).toFixed(1);

        document.getElementById('resHeaderDistance').innerHTML = `${formattedDistNm} NM <small class="text-muted font-normal" style="font-size: 0.75rem;">(${formattedDistKm} km)</small>`;
        document.getElementById('resHeaderEta').innerText = `${data.summary.eta_days} Days`;
        document.getElementById('resHeaderRiskScore').innerText = `${data.summary.risk_score} / 100`;

        // 2. Update Categorized 4 Panels
        document.getElementById('resDistanceVal').innerText = `${formattedDistNm} NM`;
        document.getElementById('resDistanceKm').innerText = `(${formattedDistKm} km)`;
        document.getElementById('resEtaVal').innerText = `${data.summary.eta_days} Days`;

        document.getElementById('resRiskVal').innerText = `${data.summary.risk_score}`;
        const riskBadge = document.getElementById('resRiskBadge');
        riskBadge.innerText = `${data.summary.risk_level} Risk`;
        riskBadge.className = data.summary.risk_level === 'Low' ? 'badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 rounded-pill small fw-semibold px-2.5 py-1' :
                              (data.summary.risk_level === 'Medium' ? 'badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-20 rounded-pill small fw-semibold px-2.5 py-1' :
                              'badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20 rounded-pill small fw-semibold px-2.5 py-1');

        document.getElementById('resCongestionVal').innerText = data.summary.port_congestion;
        document.getElementById('resWeatherVal').innerText = data.summary.weather_summary;
        document.getElementById('resCurrencyVal').innerText = data.summary.currency_impact;
        document.getElementById('resGeopoliticalVal').innerText = data.summary.geopolitical_status;
        document.getElementById('resRecommendationVal').innerText = data.summary.recommendation;

        // 3. Update Route Advisory Commentary
        document.getElementById('resAiInsightText').innerHTML = `
            <div class="d-flex align-items-start gap-2.5">
                <i class="bi bi-quote text-primary fs-2 leading-none me-1 opacity-40"></i>
                <div class="fw-medium">${data.ai_insight}</div>
            </div>
        `;

        // 4. Update Step-by-step Vertical Timeline
        const timelineContainer = document.getElementById('resTimelineContainer');
        let timelineHtml = '<div class="timeline-vertical-line"></div>';

        data.timeline.forEach((step) => {
            const isOrigin = step.type === 'Origin';
            const isDest = step.type === 'Destination';
            const dotColor = isOrigin ? '#EF4444' : (isDest ? '#10B981' : '#3B82F6');
            const badgeClass = isOrigin ? 'bg-danger' : (isDest ? 'bg-success' : 'bg-primary');
            const iconClass = isOrigin ? 'bi-geo-alt-fill text-danger' : (isDest ? 'bi-flag-fill text-success' : 'bi-diagram-3-fill text-primary');

            timelineHtml += `
                <div class="timeline-step-card" onclick="focusRouteNode(${step.latitude}, ${step.longitude}, ${step.port_id})">
                    <div class="timeline-step-dot" style="background-color: ${dotColor};"></div>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi ${iconClass} fs-6"></i>
                            <span class="badge ${badgeClass} bg-opacity-10 text-dark small fw-bold px-2 py-0.5 rounded">${step.type}</span>
                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.92rem;">${step.port_name}</h6>
                        </div>
                        <span class="badge ${step.risk_level === 'Low' ? 'bg-success' : (step.risk_level === 'Medium' ? 'bg-warning' : 'bg-danger')} bg-opacity-10 text-dark small fw-bold px-2.5 py-1 rounded-pill" style="font-size: 0.7rem;">
                            Risk: ${step.risk_score}
                        </span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between text-muted small mt-2 pt-1 border-top border-light" style="font-size: 0.78rem;">
                        <span><i class="bi bi-globe me-1"></i>${step.country_name}</span>
                        <span><i class="bi bi-bar-chart-steps me-1"></i>Congestion: <strong class="text-dark">${step.congestion}</strong></span>
                    </div>
                </div>
            `;
        });
        timelineContainer.innerHTML = timelineHtml;

        // 5. Update Alternative Route Comparison if available
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

        // 6. Draw Map Polyline Route & Waypoints, and smooth focus to Map
        drawRouteOnMap(data);

        // Smooth scroll focus to Leaflet Map hero container
        const mapHero = document.querySelector('.map-container-hero');
        if (mapHero) {
            mapHero.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    let vesselAnimationInterval = null;

    function calculateHeading(p1, p2) {
        if (!p1 || !p2) return 0;
        const lat1 = p1[0] * Math.PI / 180;
        const lat2 = p2[0] * Math.PI / 180;
        const dLon = (p2[1] - p1[1]) * Math.PI / 180;
        const y = Math.sin(dLon) * Math.cos(lat2);
        const x = Math.cos(lat1) * Math.sin(lat2) - Math.sin(lat1) * Math.cos(lat2) * Math.cos(dLon);
        const brng = Math.atan2(y, x);
        return (brng * 180 / Math.PI + 360) % 360;
    }

    function createVesselSvgIcon(headingDegree) {
        return L.divIcon({
            html: `<div style="transform: rotate(${headingDegree}deg); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; transition: transform 0.15s linear;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="filter: drop-shadow(0px 2px 4px rgba(0,0,0,0.35));">
                    <path d="M12 2L17.5 14.5H6.5L12 2Z" fill="#2563EB" stroke="#FFFFFF" stroke-width="1.5"/>
                    <path d="M5.5 14H18.5L17.5 19.5C17.5 20.6 16.6 21.5 15.5 21.5H8.5C7.4 21.5 6.5 20.6 6.5 19.5L5.5 14Z" fill="#0F172A" stroke="#FFFFFF" stroke-width="1.5"/>
                </svg>
            </div>`,
            className: 'modern-ship-vessel-icon',
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        });
    }

    function drawRouteOnMap(data) {
        routeLayersGroup.clearLayers();
        if (vesselAnimationInterval) {
            clearInterval(vesselAnimationInterval);
            vesselAnimationInterval = null;
        }

        const seaPolylineCoords = (data.sea_polyline && data.sea_polyline.length > 0) 
            ? data.sea_polyline 
            : data.timeline.map(n => [n.latitude, n.longitude]);

        // 1. Draw Primary Curved Sea Polyline (Ocean Blue #2563EB, 3.5px weight, crisp ocean flow)
        const primaryPolyline = L.polyline(seaPolylineCoords, {
            color: '#2563EB',
            weight: 3.5,
            opacity: 0.9,
            lineCap: 'round',
            lineJoin: 'round',
            className: 'animated-polyline'
        }).addTo(routeLayersGroup);

        // 2. Draw Alternative Sea Route Polyline (Emerald #059669, 3.5px dashed line)
        if (data.alternative_route && data.alternative_route.alternative.coordinates) {
            const altCoords = data.alternative_route.alternative.coordinates;
            L.polyline(altCoords, {
                color: '#059669',
                weight: 3.5,
                opacity: 0.85,
                lineCap: 'round',
                lineJoin: 'round',
                dashArray: '6, 6'
            }).addTo(routeLayersGroup).bindPopup(`
                <div class="p-2 text-dark" style="font-family: 'Outfit', sans-serif;">
                    <span class="badge bg-success mb-1">Recommended Alternative Bypass</span>
                    <h6 class="fw-bold mb-1" style="font-size: 0.9rem;">${data.alternative_route.alternative.route_summary}</h6>
                    <small class="text-muted d-block" style="font-size: 0.76rem;">${data.alternative_route.alternative.recommendation_text}</small>
                </div>
            `, { className: 'premium-leaflet-popup' });
        }

        // 3. Draw Waypoint Markers (Origin, Transit, Chokepoint, Destination)
        data.timeline.forEach(node => {
            const latLng = [node.latitude, node.longitude];

            const iconHtml = node.type === 'Origin' 
                ? `<div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center shadow border border-white" style="width: 26px; height: 26px; font-weight: 700; font-size: 13px;"><i class="bi bi-geo-alt-fill"></i></div>`
                : (node.type === 'Destination' 
                    ? `<div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center shadow border border-white" style="width: 26px; height: 26px; font-weight: 700; font-size: 13px;"><i class="bi bi-flag-fill"></i></div>`
                    : `<div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow border border-white" style="width: 22px; height: 22px; font-weight: 700; font-size: 11px;"><i class="bi bi-diagram-3-fill"></i></div>`);

            const customIcon = L.divIcon({
                html: iconHtml,
                className: 'custom-route-marker-icon',
                iconSize: [26, 26],
                iconAnchor: [13, 13]
            });

            const popupHtml = `
                <div class="p-2 text-dark" style="font-family: 'Outfit', sans-serif; min-width: 230px;">
                    <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                        <span class="badge ${node.type === 'Origin' ? 'bg-danger' : (node.type === 'Destination' ? 'bg-success' : 'bg-primary')} px-2 py-0.5 rounded">${node.type}</span>
                        <span class="fw-bold small text-dark">${node.port_name}</span>
                    </div>
                    <div class="text-muted small" style="font-size: 0.78rem; line-height: 1.6;">
                        <div>Territory: <strong class="text-dark">${node.country_name}</strong></div>
                        <div>Status: <strong class="text-success">${node.status}</strong></div>
                        <div>Congestion Index: <strong class="text-dark">${node.congestion}</strong></div>
                        <div>Weather: <strong class="text-dark">${node.weather}</strong></div>
                        <div>Risk Rating: <strong class="text-dark">${node.risk_score} (${node.risk_level})</strong></div>
                        ${node.warning ? `<div class="mt-1 text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i>${node.warning}</div>` : ''}
                    </div>
                </div>
            `;

            const marker = L.marker(latLng, { icon: customIcon }).bindPopup(popupHtml, { className: 'premium-leaflet-popup' });
            routeLayersGroup.addLayer(marker);
        });

        // 4. Draw Warning Badges for Chokepoints
        if (data.warnings && data.warnings.length > 0) {
            data.warnings.forEach(warn => {
                const warnIcon = L.divIcon({
                    html: `<div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center shadow border border-dark border-2" style="width: 24px; height: 24px; font-weight: 800; font-size: 12px;"><i class="bi bi-exclamation-triangle-fill"></i></div>`,
                    className: 'warning-chokepoint-icon',
                    iconSize: [24, 24],
                    iconAnchor: [12, 12]
                });

                const warnPopup = `
                    <div class="p-2 text-dark" style="font-family: 'Outfit', sans-serif;">
                        <span class="badge bg-warning text-dark mb-1"><i class="bi bi-shield-exclamation me-1"></i>Maritime Advisory Warning</span>
                        <h6 class="fw-bold text-dark mb-1" style="font-size: 0.88rem;">${warn.name}</h6>
                        <p class="text-danger small mb-0 fw-semibold" style="font-size: 0.76rem;">${warn.warning}</p>
                    </div>
                `;

                const warnMarker = L.marker([warn.lat, warn.lng], { icon: warnIcon }).bindPopup(warnPopup, { className: 'premium-leaflet-popup' });
                routeLayersGroup.addLayer(warnMarker);
            });
        }

        // 5. Add Sleek Modern SVG Ship Vessel Marker (24x24px) with Heading Rotation & Smooth Motion
        if (seaPolylineCoords.length > 1) {
            const initialHeading = calculateHeading(seaPolylineCoords[0], seaPolylineCoords[1]);
            const shipMarker = L.marker(seaPolylineCoords[0], {
                icon: createVesselSvgIcon(initialHeading),
                zIndexOffset: 3000
            }).addTo(routeLayersGroup);

            shipMarker.bindPopup(`
                <div class="p-2 text-dark fw-bold small text-center" style="font-family: 'Outfit', sans-serif;">
                    <i class="bi bi-water text-primary me-1"></i>Active Maritime Vessel Transit
                </div>
            `, { className: 'premium-leaflet-popup' });

            let stepIdx = 0;
            vesselAnimationInterval = setInterval(() => {
                stepIdx = (stepIdx + 1) % seaPolylineCoords.length;
                const currPt = seaPolylineCoords[stepIdx];
                const nextPt = seaPolylineCoords[(stepIdx + 1) % seaPolylineCoords.length];
                const heading = calculateHeading(currPt, nextPt);

                shipMarker.setLatLng(currPt);
                shipMarker.setIcon(createVesselSvgIcon(heading));
            }, 100);
        }

        // Fit map bounds to encompass the full sea polyline smoothly
        if (seaPolylineCoords.length > 0) {
            map.fitBounds(primaryPolyline.getBounds(), { padding: [50, 50] });
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
