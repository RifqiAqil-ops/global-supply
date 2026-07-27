<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Country;
use App\Models\CountryRiskScore;
use App\Models\ExchangeRate;
use App\Models\NewsArticle;
use App\Models\WeatherData;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class LiveApiController extends Controller
{
    /**
     * Live metrics endpoint for user dashboard.
     */
    public function dashboardMetrics(): JsonResponse
    {
        $countriesMonitored = Country::count();
        $extremeWeatherCount = WeatherData::where('is_extreme', true)->count();
        $currenciesCount = ExchangeRate::distinct('currency_code')->count();
        $avgRisk = round((float) (CountryRiskScore::avg('composite_score') ?? 0), 2);

        $topRiskCountries = CountryRiskScore::with('country')
            ->orderByDesc('composite_score')
            ->limit(5)
            ->get()
            ->map(function ($score) {
                return [
                    'iso2' => $score->country->iso2 ?? '',
                    'country_name' => $score->country->name ?? 'Unknown',
                    'country_flag' => $score->country->flag_url ?? '',
                    'composite_score' => round((float) $score->composite_score, 2),
                    'risk_level' => $score->risk_level,
                    'score_change' => (float) $score->score_change,
                ];
            });

        $topHighestRisk = CountryRiskScore::with('country')
            ->orderByDesc('composite_score')
            ->limit(10)
            ->get()
            ->map(function ($score) {
                return [
                    'iso2' => $score->country->iso2 ?? '',
                    'country_name' => $score->country->name ?? 'Unknown',
                    'country_flag' => $score->country->flag_url ?? '',
                    'composite_score' => round((float) $score->composite_score, 2),
                    'risk_level' => $score->risk_level,
                ];
            });

        $topLowestRisk = CountryRiskScore::with('country')
            ->orderBy('composite_score')
            ->limit(10)
            ->get()
            ->map(function ($score) {
                return [
                    'iso2' => $score->country->iso2 ?? '',
                    'country_name' => $score->country->name ?? 'Unknown',
                    'country_flag' => $score->country->flag_url ?? '',
                    'composite_score' => round((float) $score->composite_score, 2),
                    'risk_level' => $score->risk_level,
                ];
            });

        $recentChanges = CountryRiskScore::with('country')
            ->where('score_change', '!=', 0)
            ->orderByDesc('calculated_at')
            ->limit(5)
            ->get()
            ->map(function ($score) {
                $prev = (float) $score->composite_score - (float) $score->score_change;
                return [
                    'iso2' => $score->country->iso2 ?? '',
                    'country_name' => $score->country->name ?? 'Unknown',
                    'country_flag' => $score->country->flag_url ?? '',
                    'prev_score' => round($prev, 2),
                    'new_score' => round((float) $score->composite_score, 2),
                    'change' => (float) $score->score_change,
                ];
            });

        $recentAlerts = ActivityLog::where('action', 'risk_alert')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function ($log) {
                return [
                    'description' => $log->description,
                    'time_ago' => $log->created_at ? $log->created_at->diffForHumans() : 'Just now',
                    'old_score' => $log->properties['old_score'] ?? 'N/A',
                    'new_score' => $log->properties['new_score'] ?? 'N/A',
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => [
                'avgRisk' => number_format($avgRisk, 2),
                'countriesMonitored' => $countriesMonitored,
                'extremeWeatherCount' => $extremeWeatherCount,
                'currenciesCount' => $currenciesCount,
                'topRiskCountries' => $topRiskCountries,
                'topHighestRisk' => $topHighestRisk,
                'topLowestRisk' => $topLowestRisk,
                'recentChanges' => $recentChanges,
                'recentAlerts' => $recentAlerts,
            ]
        ]);
    }

    /**
     * Live weather API endpoint.
     */
    public function weather(): JsonResponse
    {
        $weatherEntries = WeatherData::with('country')
            ->select('weather_data.*')
            ->join(
                DB::raw('(SELECT MAX(id) as max_id FROM weather_data GROUP BY country_id) as latest'),
                'weather_data.id', '=', 'latest.max_id'
            )
            ->get();

        $entries = $weatherEntries->map(function ($w) {
            return [
                'country_name' => $w->country->name ?? 'Unknown',
                'iso2' => $w->country->iso2 ?? '',
                'country_flag' => $w->country->flag_url ?? '',
                'temperature' => round((float) $w->temperature, 1),
                'feels_like' => round((float) $w->feels_like, 1),
                'humidity' => round((float) $w->humidity),
                'wind_speed' => round((float) $w->wind_speed, 1),
                'precipitation' => round((float) $w->precipitation, 1),
                'uv_index' => round((float) $w->uv_index, 1),
                'weather_description' => $w->weather_description ?? 'N/A',
                'is_extreme' => (bool) $w->is_extreme,
                'fetched_at' => $w->fetched_at ? $w->fetched_at->diffForHumans() : 'N/A',
            ];
        });

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
                'precipitation' => round((float) $w->precipitation, 1),
                'uv_index' => round((float) $w->uv_index, 1),
                'weather_desc' => $w->weather_description ?? 'N/A',
                'is_extreme' => (bool) $w->is_extreme,
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'data' => [
                'totalStations' => $weatherEntries->count(),
                'extremeCount' => $weatherEntries->where('is_extreme', true)->count(),
                'avgTemp' => round($weatherEntries->avg('temperature'), 1),
                'avgHumidity' => round($weatherEntries->avg('humidity')),
                'entries' => $entries,
                'mapMarkers' => $mapMarkers,
            ]
        ]);
    }

    /**
     * Live exchange rates API endpoint.
     */
    public function exchangeRates(): JsonResponse
    {
        $latestRates = ExchangeRate::with('country')
            ->select('exchange_rates.*')
            ->join(
                DB::raw('(SELECT currency_code, MAX(rate_date) as max_date FROM exchange_rates GROUP BY currency_code) as latest'),
                function($join) {
                    $join->on('exchange_rates.currency_code', '=', 'latest.currency_code')
                         ->on('exchange_rates.rate_date', '=', 'latest.max_date');
                }
            )
            ->orderBy('currency_code')
            ->get();

        $rates = $latestRates->map(function ($r) {
            return [
                'currency_code' => $r->currency_code,
                'currency_name' => $r->currency_name ?? '',
                'country_name' => $r->country->name ?? '',
                'country_flag' => $r->country->flag_url ?? '',
                'rate_to_usd' => number_format((float) $r->rate_to_usd, 4),
                'rate_to_idr' => number_format((float) $r->rate_to_idr, 2),
                'change_percent' => $r->change_percent !== null ? number_format((float) $r->change_percent, 2) : null,
                'rate_date' => $r->rate_date ? \Carbon\Carbon::parse($r->rate_date)->format('Y-m-d') : 'N/A',
            ];
        });

        $topGainers = $latestRates->sortByDesc('change_percent')->take(5)->map(function($g) {
            return [
                'currency_code' => $g->currency_code,
                'country_flag' => $g->country->flag_url ?? '',
                'change_percent' => number_format((float) $g->change_percent, 2),
            ];
        })->values();

        $topLosers = $latestRates->sortBy('change_percent')->take(5)->map(function($l) {
            return [
                'currency_code' => $l->currency_code,
                'country_flag' => $l->country->flag_url ?? '',
                'change_percent' => number_format((float) $l->change_percent, 2),
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'data' => [
                'totalCurrencies' => $latestRates->unique('currency_code')->count(),
                'avgChange' => number_format((float) $latestRates->avg('change_percent'), 2),
                'topGainers' => $topGainers,
                'topLosers' => $topLosers,
                'rates' => $rates,
            ]
        ]);
    }

    /**
     * Live news API endpoint.
     */
    public function news(): JsonResponse
    {
        $articles = NewsArticle::with('country')
            ->whereNotNull('source_url')
            ->where('source_url', '!=', '')
            ->where('source_url', 'not like', '%example.com%')
            ->orderByDesc('published_at')
            ->limit(12)
            ->get()
            ->map(function ($a) {
                return [
                    'id' => $a->id,
                    'title' => $a->title,
                    'description' => $a->description,
                    'sentiment' => $a->sentiment ?? 'neutral',
                    'source_url' => $a->source_url,
                    'image_url' => $a->image_url,
                    'country_name' => $a->country->name ?? '',
                    'country_flag' => $a->country->flag_url ?? '',
                    'published_at' => $a->published_at ? $a->published_at->diffForHumans() : 'Recently',
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $articles
        ]);
    }

    /**
     * Live country risk details breakdown endpoint.
     */
    public function countryRisk(string $code): JsonResponse
    {
        $code = strtoupper(trim($code));
        $country = Country::where('iso2', $code)->orWhere('iso3', $code)->first();
        if (!$country) {
            return response()->json(['status' => 'error', 'message' => 'Country not found'], 404);
        }

        $riskScore = CountryRiskScore::with('details.riskCategory')
            ->where('country_id', $country->id)
            ->latest('calculated_at')
            ->first();

        if (!$riskScore) {
            return response()->json(['status' => 'error', 'message' => 'No risk score available'], 404);
        }

        $details = $riskScore->details->map(function ($d) {
            return [
                'category_name' => $d->riskCategory->name ?? 'Category',
                'category_slug' => \Illuminate\Support\Str::slug($d->riskCategory->name ?? 'category'),
                'category_score' => (float) $d->category_score,
                'weight' => (float) $d->weight_used,
                'weighted_score' => (float) $d->weighted_score,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'iso2' => $country->iso2,
                'country_name' => $country->name,
                'composite_score' => round((float) $riskScore->composite_score, 2),
                'risk_level' => $riskScore->risk_level,
                'calculated_at' => $riskScore->calculated_at ? $riskScore->calculated_at->diffForHumans() : 'Just now',
                'details' => $details,
            ]
        ]);
    }
}
