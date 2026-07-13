@extends('layouts.app')

@section('title', 'Global Weather Monitoring')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #weatherMap { height: 520px; border-radius: 8px; background: #1a1e2e; }
    .leaflet-popup-content-wrapper { background: #1e293b; color: #e2e8f0; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.4); }
    .leaflet-popup-tip { background: #1e293b; }
    .leaflet-popup-content { margin: 12px 16px; }
    .weather-popup-title { font-weight: 700; font-size: 14px; margin-bottom: 6px; }
    .weather-popup-metric { font-size: 12px; color: #94a3b8; line-height: 1.8; }
    .weather-popup-metric strong { color: #f1f5f9; }
    .extreme-marker { border: 2px solid #ef4444 !important; }
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

<!-- Leaflet Map -->
<x-card title="Interactive Weather Map" icon="bi-map">
    <div id="weatherMap"></div>
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
    });
    window.weatherMapInstance = map;

    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '© OpenStreetMap contributors © CARTO',
        subdomains: 'abcd',
        maxZoom: 19
    }).addTo(map);

    const markersGroup = L.layerGroup().addTo(map);
    window.weatherMarkersGroup = markersGroup;

    window.updateWeatherMarkers = function(markers) {
        markersGroup.clearLayers();
        markers.forEach(m => {
            if (!m.lat || !m.lng) return;

            const isExtreme = m.is_extreme;
            const iconColor = isExtreme ? '#ef4444' : (m.temp > 35 ? '#f97316' : (m.temp < 0 ? '#3b82f6' : '#22c55e'));

            const icon = L.divIcon({
                className: 'custom-weather-marker',
                html: `<div style="
                    background: ${iconColor};
                    width: 12px; height: 12px; border-radius: 50%;
                    border: 2px solid ${isExtreme ? '#fca5a5' : 'rgba(255,255,255,0.3)'};
                    box-shadow: 0 0 ${isExtreme ? '8' : '4'}px ${iconColor};
                "></div>`,
                iconSize: [12, 12],
                iconAnchor: [6, 6],
            });

            const popup = `
                <div class="weather-popup-title">${m.flag ? `<img src="${m.flag}" style="width:16px;height:11px;border-radius:2px;margin-right:4px;">` : ''}${m.name}</div>
                <div class="weather-popup-metric">
                    🌡️ Temperature: <strong>${m.temp}°C</strong> (Feels ${m.feels_like}°C)<br>
                    💧 Humidity: <strong>${m.humidity}%</strong><br>
                    💨 Wind: <strong>${m.wind_speed} km/h</strong> (${m.wind_dir}°)<br>
                    🌧️ Precipitation: <strong>${m.precipitation} mm</strong><br>
                    ☀️ UV Index: <strong>${m.uv_index}</strong><br>
                    📡 Condition: <strong>${m.weather_desc}</strong><br>
                    ${isExtreme ? '<span style="color:#ef4444;font-weight:700;">⚠ EXTREME WEATHER ALERT</span><br>' : ''}
                    <span style="color:#64748b;font-size:11px;">Updated: ${m.fetched_at}</span>
                </div>
            `;

            L.marker([m.lat, m.lng], { icon })
                .addTo(markersGroup)
                .bindPopup(popup, { maxWidth: 260 });
        });
    };

    const markers = @json($mapMarkers);
    window.updateWeatherMarkers(markers);
});
</script>
@endpush
