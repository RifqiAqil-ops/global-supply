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

        // Get some countries with their indicators and weather
        $watchlistCountries = Country::whereIn('iso2', ['US', 'CN', 'IN', 'PH'])
            ->with(['weatherData'])
            ->get();

        return view('user.dashboard', compact('countriesMonitored', 'extremeWeatherCount', 'currenciesCount', 'watchlistCountries'));
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
