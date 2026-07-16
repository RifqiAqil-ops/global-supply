@extends('layouts.app')

@section('title', 'Global Weather Monitoring')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #weatherMap { height: 100%; width: 100%; border-radius: 0 0 24px 24px; background: #F8FAFC; }
    .leaflet-popup-content-wrapper { background: #FFFFFF !important; color: #334155 !important; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); border: 1px solid #E5E7EB; }
    .leaflet-popup-tip { background: #FFFFFF !important; }
    .leaflet-popup-content { margin: 12px 16px; }
    .weather-popup-title { font-weight: 700; font-size: 14px; margin-bottom: 6px; color: #0F172A !important; }
    .weather-popup-metric { font-size: 12px; color: #64748B; line-height: 1.8; }
    .weather-popup-metric strong { color: #0F172A !important; }
    .extreme-marker { border: 2px solid #ef4444 !important; }
    .map-floating-overlay { position: absolute; z-index: 1000; pointer-events: auto; }
    .pulse-dot { width: 8px; height: 8px; background-color: #10B981; border-radius: 50%; display: inline-block; box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); animation: pulse 1.6s infinite; }
    @keyframes pulse {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
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
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 text-white mb-1">Global Weather Monitoring</h1>
        <p class="text-muted small mb-0">Live weather intelligence for supply chain risk assessment across all monitored countries.</p>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <x-stat-card title="Monitoring Stations" :value="$totalStations" icon="bi-geo-alt" color="primary" valueId="stat-weather-stations" />
    </div>
    <div class="col-md-3">
        <x-stat-card title="Extreme Weather Alerts" :value="$extremeCount" icon="bi-exclamation-triangle" color="danger" valueId="stat-weather-alerts" />
    </div>
    <div class="col-md-3">
        <x-stat-card title="Global Avg Temperature" :value="$avgTemp . '°C'" icon="bi-thermometer-half" color="warning" valueId="stat-weather-avg-temp" />
    </div>
    <div class="col-md-3">
        <x-stat-card title="Global Avg Humidity" :value="$avgHumidity . '%'" icon="bi-droplet" color="info" valueId="stat-weather-avg-humidity" />
    </div>
</div>

<x-card title="Interactive Weather Map" icon="bi-map" class="p-0 position-relative overflow-hidden">
    <div class="d-flex flex-wrap gap-2 px-3 py-2 border-bottom" style="background-color: #F8FAFC; border-color: #E5E7EB !important; z-index: 10;">
        <button class="btn btn-sm btn-primary rounded-pill filter-chip-weather px-3" data-filter="all" style="font-size: 0.76rem; font-weight: 600;">All Conditions</button>
        <button class="btn btn-sm btn-light rounded-pill filter-chip-weather px-3 text-success" data-filter="normal" style="font-size: 0.76rem; font-weight: 600; border-color: #E2E8F0;">🟢 Normal Temp</button>
        <button class="btn btn-sm btn-light rounded-pill filter-chip-weather px-3 text-warning" data-filter="heat" style="font-size: 0.76rem; font-weight: 600; border-color: #E2E8F0;">🟡 High Heat</button>
        <button class="btn btn-sm btn-light rounded-pill filter-chip-weather px-3 text-danger" data-filter="extreme" style="font-size: 0.76rem; font-weight: 600; border-color: #E2E8F0;">🔴 Extreme Alert</button>
    </div>
    <div class="position-relative" style="height: 520px; border-radius: 0 0 24px 24px;">
        <!-- Map skeleton -->
        <div id="mapSkeleton" class="position-absolute top-0 start-0 w-100 h-100 bg-white d-flex flex-column gap-3 p-4" style="z-index: 10000; transition: opacity 0.4s ease;">
            <div class="skeleton-shimmer rounded-3" style="height: 32px; width: 40%; background: linear-gradient(90deg, #F1F5F9 25%, #E2E8F0 50%, #F1F5F9 75%); background-size: 200% 100%; animation: shimmer 1.5s infinite;"></div>
            <div class="skeleton-shimmer rounded-4 flex-grow-1" style="background: linear-gradient(90deg, #F1F5F9 25%, #E2E8F0 50%, #F1F5F9 75%); background-size: 200% 100%; animation: shimmer 1.5s infinite;"></div>
        </div>

        <!-- Map div -->
        <div id="weatherMap" style="width: 100%; height: 100%; z-index: 1; border-radius: 0 0 24px 24px;"></div>
        
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
        <div class="map-floating-overlay bottom-0 start-0 m-3 p-3 bg-white rounded-3 shadow-sm border d-none d-sm-block" style="border-color: #E5E7EB !important; max-width: 240px; z-index: 1000;">
            <h6 class="text-dark small fw-bold mb-2">Weather Condition Indicators</h6>
            <div class="d-flex flex-column gap-1 text-muted small" style="font-size: 0.72rem;">
                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-block rounded-circle" style="width: 10px; height: 10px; background-color: #22c55e;"></span> Normal (&lt; 35°C)
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-block rounded-circle" style="width: 10px; height: 10px; background-color: #f97316;"></span> High Heat (&gt; 35°C)
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-block rounded-circle" style="width: 10px; height: 10px; background-color: #ef4444; border: 1.5px solid #ef4444;"></span> Extreme Front Alert
                </div>
            </div>
        </div>
    </div>
</x-card>

<!-- Weather Table -->
<div class="mt-4">
    <x-card title="Weather Data Summary" icon="bi-cloud-sun">
        <div class="table-responsive">
            <x-table :headers="['Country', 'Temperature', 'Feels Like', 'Humidity', 'Wind', 'Precipitation', 'UV Index', 'Condition', 'Extreme', 'Updated']" tbodyId="weather-table-tbody">
                @foreach($weatherEntries->sortByDesc('is_extreme')->take(50) as $w)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2 small">
                            @if($w->country)
                            <img src="{{ $w->country->flag_url }}" class="rounded" style="width:18px;height:12px;object-fit:cover;" alt="">
                            <a href="{{ route('countries.show', $w->country->iso2) }}" class="text-white text-decoration-none hover-primary">{{ $w->country->name }}</a>
                            @endif
                        </div>
                    </td>
                    <td class="text-white fw-semibold">{{ $w->temperature }}°C</td>
                    <td class="text-muted small">{{ $w->feels_like }}°C</td>
                    <td class="text-muted small">{{ $w->humidity }}%</td>
                    <td class="text-muted small">{{ $w->wind_speed }} km/h</td>
                    <td class="text-muted small">{{ $w->precipitation }} mm</td>
                    <td class="text-muted small">{{ $w->uv_index }}</td>
                    <td class="text-muted small">{{ $w->weather_description }}</td>
                    <td>
                        @if($w->is_extreme)
                            <x-badge type="danger">⚠ Extreme</x-badge>
                        @else
                            <x-badge type="success">Normal</x-badge>
                        @endif
                    </td>
                    <td class="text-muted small">{{ $w->fetched_at ? $w->fetched_at->diffForHumans() : '—' }}</td>
                </tr>
                @endforeach
            </x-table>
        </div>
    </x-card>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const map = L.map('weatherMap', {
        center: [20, 0],
        zoom: 2,
        minZoom: 2,
        maxZoom: 10,
        scrollWheelZoom: true,
        zoomControl: false
    });
    window.weatherMapInstance = map;

    L.control.zoom({ position: 'topleft' }).addTo(map);

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

    const markersGroup = L.layerGroup().addTo(map);
    window.weatherMarkersGroup = markersGroup;

    window.updateWeatherMarkers = function(markersData) {
        markersGroup.clearLayers();
        markersData.forEach(m => {
            if (!m.lat || !m.lng) return;

            const isExtreme = m.is_extreme;
            const iconColor = isExtreme ? '#ef4444' : (m.temp > 35 ? '#f97316' : (m.temp < 0 ? '#3b82f6' : '#22c55e'));

            const icon = L.divIcon({
                className: 'custom-weather-marker',
                html: `<div style="
                    background: ${iconColor};
                    width: 12.5px; height: 12.5px; border-radius: 50%;
                    border: 2px solid ${isExtreme ? '#fca5a5' : 'rgba(255,255,255,0.45)'};
                    box-shadow: 0 0 ${isExtreme ? '8' : '4'}px ${iconColor};
                "></div>`,
                iconSize: [13, 13],
                iconAnchor: [6.5, 6.5],
            });

            const popup = `
                <div class="p-2 text-dark" style="font-family: 'Outfit', sans-serif; min-width: 210px;">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        ${m.flag ? `<img src="${m.flag}" style="width:20px;height:13px;border-radius:2px;object-fit:cover;" class="border shadow-sm">` : ''}
                        <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.88rem;">${m.name}</h6>
                    </div>
                    <div class="mb-2">
                        <span class="badge ${isExtreme ? 'bg-danger' : 'bg-success'} text-white px-2.5 py-0.5 small" style="font-size: 0.72rem; font-weight: 600;">
                            ${isExtreme ? '⚠ EXTREME WARNING' : 'NORMAL CONDITIONS'}
                        </span>
                    </div>
                    <div class="text-muted small mb-2" style="font-size: 0.75rem; line-height: 1.55;">
                        🌡️ Temp: <strong>${m.temp}°C</strong> (Feels ${m.feels_like}°C)<br>
                        💧 Humidity: <strong>${m.humidity}%</strong><br>
                        💨 Wind: <strong>${m.wind_speed} km/h</strong><br>
                        🌧️ Precipitation: <strong>${m.precipitation} mm</strong><br>
                        📡 Condition: <strong>${m.weather_desc}</strong>
                    </div>
                    <a href="/countries/${m.iso2 || 'US'}" class="btn btn-primary btn-sm w-100 rounded-pill text-white fw-bold py-1 text-center text-decoration-none" style="font-size: 0.72rem;">
                        <i class="bi bi-eye-fill me-1"></i> View Profile
                    </a>
                </div>
            `;

            const marker = L.marker([m.lat, m.lng], { icon })
                .addTo(markersGroup)
                .bindPopup(popup, { className: 'premium-leaflet-popup' });

            const urlParams = new URLSearchParams(window.location.search);
            const searchVal = urlParams.get('search');
            if (searchVal && m.name.toLowerCase() === searchVal.toLowerCase()) {
                map.setView([m.lat, m.lng], 6);
                setTimeout(() => {
                    marker.openPopup();
                }, 450);
            }
        });
    };

    const markers = @json($mapMarkers);
    window.updateWeatherMarkers(markers);

    // Map Control actions
    document.getElementById('mapBtnFullscreen').addEventListener('click', function() {
        const mapContainer = document.getElementById('weatherMap').parentElement;
        if (!document.fullscreenElement) {
            mapContainer.requestFullscreen().catch(err => {
                console.error(`Error enabling fullscreen: ${err.message}`);
            });
        } else {
            document.exitFullscreen();
        }
    });

    document.getElementById('mapBtnReset').addEventListener('click', function() {
        map.setView([20, 0], 2);
    });

    document.getElementById('mapBtnLocation').addEventListener('click', function() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(position => {
                map.setView([position.coords.latitude, position.coords.longitude], 10);
            }, err => {
                alert("Unable to retrieve location.");
            });
        } else {
            alert("Geolocation is not supported by your browser.");
        }
    });

    // Filter chips actions
    document.querySelectorAll('.filter-chip-weather').forEach(chip => {
        chip.addEventListener('click', function() {
            document.querySelectorAll('.filter-chip-weather').forEach(c => {
                c.classList.remove('btn-primary', 'active');
                c.classList.add('btn-light');
            });
            this.classList.add('btn-primary', 'active');
            this.classList.remove('btn-light');

            const filterVal = this.getAttribute('data-filter');
            const filtered = markers.filter(m => {
                if (filterVal === 'all') return true;
                if (filterVal === 'extreme') return m.is_extreme;
                if (filterVal === 'heat') return m.temp > 35;
                if (filterVal === 'normal') return !m.is_extreme && m.temp <= 35;
                return true;
            });
            window.updateWeatherMarkers(filtered);
        });
    });
});
</script>
@endpush
