<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\WeatherData;
use Illuminate\Http\Request;

class WeatherController extends Controller
{
    /**
     * Show the Global Weather Monitoring dashboard with Leaflet map.
     */
    public function index()
    {
        // Get latest weather data per country with coordinates
        $weatherEntries = WeatherData::with('country')
            ->select('weather_data.*')
            ->join(
                \DB::raw('(SELECT MAX(id) as max_id FROM weather_data GROUP BY country_id) as latest'),
                'weather_data.id', '=', 'latest.max_id'
            )
            ->get();

        // Build map markers JSON
        $mapMarkers = $weatherEntries->map(function ($w) {
            return [
                'lat' => (float) $w->latitude,
                'lng' => (float) $w->longitude,
                'name' => $w->country->name ?? 'Unknown',
                'iso2' => $w->country->iso2 ?? '',
                'flag' => $w->country->flag_url ?? '',
                'temp' => round((float) $w->temperature, 1),
                'feels_like' => round((float) $w->feels_like, 1),
                'humidity' => round((float) $w->humidity),
                'wind_speed' => round((float) $w->wind_speed, 1),
                'wind_dir' => (int) $w->wind_direction,
                'precipitation' => round((float) $w->precipitation, 1),
                'pressure' => round((float) $w->pressure),
                'uv_index' => round((float) $w->uv_index, 1),
                'weather_desc' => $w->weather_description ?? 'N/A',
                'weather_code' => (int) $w->weather_code,
                'is_extreme' => (bool) $w->is_extreme,
                'fetched_at' => $w->fetched_at ? $w->fetched_at->diffForHumans() : 'N/A',
            ];
        })->values();

        // Stats
        $totalStations = $weatherEntries->count();
        $extremeCount = $weatherEntries->where('is_extreme', true)->count();
        $avgTemp = round($weatherEntries->avg('temperature'), 1);
        $avgHumidity = round($weatherEntries->avg('humidity'));

        return view('user.weather', compact(
            'weatherEntries', 'mapMarkers',
            'totalStations', 'extremeCount', 'avgTemp', 'avgHumidity'
        ));
    }
}
