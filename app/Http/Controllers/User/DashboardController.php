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

        // 1. Top 10 Highest Risk Countries
        $topHighestRisk = \App\Models\CountryRiskScore::with('country')
            ->orderByDesc('composite_score')
            ->limit(10)
            ->get();

        // 2. Top 10 Lowest Risk Countries
        $topLowestRisk = \App\Models\CountryRiskScore::with('country')
            ->orderBy('composite_score')
            ->limit(10)
            ->get();

        // 3. Recent Risk Changes
        $recentChanges = \App\Models\CountryRiskScore::with('country')
            ->where('score_change', '!=', 0)
            ->orderByDesc('calculated_at')
            ->limit(5)
            ->get();

        // 4. Recent Alerts
        $recentAlerts = \App\Models\ActivityLog::where('action', 'risk_alert')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('user.dashboard', compact(
            'countriesMonitored', 'extremeWeatherCount', 'currenciesCount', 
            'watchlistCountries', 'avgRisk', 'topRiskCountries',
            'topHighestRisk', 'topLowestRisk', 'recentChanges', 'recentAlerts'
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
