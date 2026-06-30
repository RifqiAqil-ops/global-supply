@extends('layouts.app')

@section('title', 'Global Port Directory')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
    #map {
        height: 450px;
        border-radius: var(--bs-card-border-radius, 8px);
        border: 1px solid var(--color-border);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    }
    .leaflet-popup-content-wrapper {
        background-color: var(--color-card-bg, #1a1d21);
        color: var(--color-text-main, #ffffff);
        border: 1px solid var(--color-border, #2d3139);
        border-radius: 6px;
    }
    .leaflet-popup-tip {
        background-color: var(--color-card-bg, #1a1d21);
    }
    .leaflet-popup-content {
        margin: 12px;
        font-family: inherit;
    }
    .leaflet-popup-content h6 {
        font-size: 0.9rem;
        margin-bottom: 4px;
        font-weight: 600;
        color: #ffffff;
    }
    .leaflet-popup-content p {
        font-size: 0.78rem;
        margin-bottom: 8px;
        color: #adb5bd;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Header Title -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 text-white mb-1 fw-bold">Ports & Logistics Dashboard</h1>
            <p class="text-muted small mb-0">Interactive Leaflet visualization mapping all global ports and shipping lanes</p>
        </div>
        <div>
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 py-2 px-3 fw-bold">
                <i class="bi bi-anchor me-1"></i> Active Ports: {{ count($mapPorts) }}
            </span>
        </div>
    </div>

    <!-- Leaflet Map Section -->
    <div class="card card-premium border-0 mb-4">
        <div class="card-header bg-transparent border-bottom py-3" style="border-color: var(--color-border) !important;">
            <h5 class="card-title text-white mb-0 fs-6 fw-semibold">
                <i class="bi bi-map-fill me-2 text-primary"></i>Global Shipping Ports Map
            </h5>
        </div>
        <div class="card-body p-3">
            <div id="map"></div>
        </div>
    </div>

    <!-- Controls Panel & Directory Grid -->
    <div class="row g-4">
        <!-- Search & Filter Controls -->
        <div class="col-lg-3">
            <div class="card card-premium border-0">
                <div class="card-header bg-transparent border-bottom py-3" style="border-color: var(--color-border) !important;">
                    <h5 class="card-title text-white mb-0 fs-6 fw-semibold">
                        <i class="bi bi-funnel me-2 text-primary"></i>Filters & Controls
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('ports.index') }}" method="GET" id="ports-filter-form">
                        <div class="d-flex flex-column gap-3">
                            <!-- Search Port/Country -->
                            <div>
                                <label class="form-label text-muted small fw-semibold">Search</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark border-secondary text-muted"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" class="form-control bg-dark border-secondary text-white" placeholder="Port name, country..." value="{{ $filters['search'] ?? '' }}">
                                </div>
                            </div>

                            <!-- Region Filter -->
                            <div>
                                <label class="form-label text-muted small fw-semibold">Region</label>
                                <select name="region" class="form-select bg-dark border-secondary text-white">
                                    <option value="">All Regions</option>
                                    <option value="Americas" {{ ($filters['region'] ?? '') === 'Americas' ? 'selected' : '' }}>Americas</option>
                                    <option value="Asia" {{ ($filters['region'] ?? '') === 'Asia' ? 'selected' : '' }}>Asia</option>
                                    <option value="Europe" {{ ($filters['region'] ?? '') === 'Europe' ? 'selected' : '' }}>Europe</option>
                                    <option value="Africa" {{ ($filters['region'] ?? '') === 'Africa' ? 'selected' : '' }}>Africa</option>
                                    <option value="Oceania" {{ ($filters['region'] ?? '') === 'Oceania' ? 'selected' : '' }}>Oceania</option>
                                </select>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex gap-2 pt-2">
                                <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-filter me-1"></i>Apply</button>
                                <a href="{{ route('ports.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-counterclockwise"></i></a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Directory Table List -->
        <div class="col-lg-9">
            <div class="card card-premium border-0">
                <div class="card-header bg-transparent border-bottom py-3" style="border-color: var(--color-border) !important;">
                    <h5 class="card-title text-white mb-0 fs-6 fw-semibold">
                        <i class="bi bi-list-ul me-2 text-primary"></i>Port Directory List
                    </h5>
                </div>
                <div class="card-body p-0">
                    <x-table :headers="['Port Name', 'Code', 'Country', 'Coordinates', 'Type', 'Size', 'Max Depth', 'Action']">
                        @forelse($ports as $port)
                        <tr>
                            <td class="align-middle"><strong>{{ $port->name }}</strong></td>
                            <td class="align-middle small"><code>{{ $port->port_code ?? 'N/A' }}</code></td>
                            <td class="align-middle">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $port->country->flag_url }}" alt="{{ $port->country->name }} Flag" class="rounded border border-secondary border-opacity-10" style="width: 24px; height: 16px; object-fit: cover;">
                                    <span class="text-white small">{{ $port->country->name }}</span>
                                </div>
                            </td>
                            <td class="align-middle small text-muted">
                                {{ number_format($port->latitude, 4) }}, {{ number_format($port->longitude, 4) }}
                            </td>
                            <td class="align-middle small text-capitalize"><span class="badge bg-secondary bg-opacity-10 text-muted border border-secondary border-opacity-10">{{ $port->port_type }}</span></td>
                            <td class="align-middle small text-capitalize"><span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10">{{ $port->port_size ?? 'N/A' }}</span></td>
                            <td class="align-middle small text-white">{{ $port->max_depth ? $port->max_depth . ' m' : 'N/A' }}</td>
                            <td class="align-middle">
                                <a href="javascript:void(0);" onclick="focusMap({{ $port->latitude }}, {{ $port->longitude }}, '{{ addslashes($port->name) }}')" class="btn btn-sm btn-link text-primary p-0 text-decoration-none fw-semibold"><i class="bi bi-geo-alt-fill me-1"></i>Focus</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No ports match your search filters.</td>
                        </tr>
                        @endforelse
                    </x-table>
                </div>

                <!-- Pagination footer -->
                @if($ports->hasPages())
                <div class="card-footer bg-transparent border-top py-3 d-flex justify-content-between align-items-center" style="border-color: var(--color-border) !important;">
                    <div class="text-muted small">
                        Showing {{ $ports->firstItem() }} to {{ $ports->lastItem() }} of {{ $ports->total() }} ports
                    </div>
                    <div>
                        {{ $ports->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    // Initialize map
    const map = L.map('map', {
        minZoom: 2,
        maxZoom: 18
    }).setView([20, 0], 2);

    // Add Dark Mode/Clean CartoDB Tile Layer
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 20
    }).addTo(map);

    // Keep track of markers in an object
    const markers = {};

    // Load active ports from backend DTO array
    const ports = @json($mapPorts);

    // Add markers for all ports
    ports.forEach(port => {
        if (port.latitude && port.longitude) {
            const popupHtml = `
                <div class="leaflet-popup-content-inner">
                    <h6>${port.name}</h6>
                    <p><strong>Country:</strong> ${port.country_name} (${port.country_code})<br>
                    <strong>Coordinates:</strong> ${port.latitude.toFixed(4)}, ${port.longitude.toFixed(4)}<br>
                    <strong>Type/Size:</strong> ${port.port_type.toUpperCase()} / ${port.port_size ? port.port_size.toUpperCase() : 'N/A'}</p>
                    <a href="${port.view_url}" class="btn btn-xs btn-primary text-white w-100 py-1 text-center small text-decoration-none fw-semibold d-block">
                        <i class="bi bi-flag-fill me-1"></i>View Country Details
                    </a>
                </div>
            `;

            // Custom green anchor icon or simple colored markers
            const marker = L.marker([port.latitude, port.longitude]).addTo(map)
                .bindPopup(popupHtml);
            
            // Store reference
            markers[`${port.latitude}_${port.longitude}`] = marker;
        }
    });

    // Helper function to focus map on a port
    function focusMap(lat, lng, name) {
        map.setView([lat, lng], 10);
        const markerKey = `${lat}_${lng}`;
        if (markers[markerKey]) {
            markers[markerKey].openPopup();
        }
        // Scroll map into view for mobile
        document.getElementById('map').scrollIntoView({ behavior: 'smooth' });
    }
</script>
@endpush
