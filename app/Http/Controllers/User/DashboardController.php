<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\ExchangeRate;
use App\Models\WeatherData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Show the User Sourcing Risk Dashboard.
     */
    public function index()
    {
        $countriesMonitored = Country::count();
        
        // Find latest weather records count or alerts
        $extremeWeatherCount = WeatherData::where('is_extreme', true)->count();
        
        // Currencies count
        $currenciesCount = ExchangeRate::distinct('currency_code')->count();

        // Calculate actual average global risk score
        $avgRisk = \App\Models\CountryRiskScore::avg('composite_score') ?? 0.0;

        // Fetch top 5 risk hotspots dynamically
        $topRiskCountries = \App\Models\CountryRiskScore::with('country')
            ->orderByDesc('composite_score')
            ->limit(5)
            ->get();

        // Get some countries with their indicators and weather
        $watchlistCountries = Country::whereIn('iso2', ['US', 'CN', 'IN', 'PH'])
            ->with(['weatherData', 'latestRiskScore'])
            ->get();

        return view('user.dashboard', compact(
            'countriesMonitored', 'extremeWeatherCount', 'currenciesCount', 
            'watchlistCountries', 'avgRisk', 'topRiskCountries'
        ));
    }

    /**
     * Refresh all cached metrics.
     */
    public function refreshMetrics(Request $request)
    {
        // Flush all general system caches
        Cache::flush();

        return redirect()->back()->with('success', 'Dashboard metrics and API cache refreshed successfully!');
    }
}
