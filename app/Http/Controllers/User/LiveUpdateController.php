<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\CountryRiskScore;
use App\Models\ExchangeRate;
use App\Models\WeatherData;
use App\Models\NewsArticle;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LiveUpdateController extends Controller
{
    /**
     * Get updated metrics and tables for the User Dashboard.
     */
    public function dashboardMetrics()
    {
        try {
            $avgRisk = CountryRiskScore::avg('composite_score') ?? 0.0;
            $countriesMonitored = Country::count();
            
            $latestWeather = WeatherData::select('weather_data.*')
                ->join(
                    \DB::raw('(SELECT MAX(id) as max_id FROM weather_data GROUP BY country_id) as latest'),
                    'weather_data.id', '=', 'latest.max_id'
                );
            $extremeWeatherCount = (clone $latestWeather)->where('is_extreme', true)->count();
            
            $currenciesCount = ExchangeRate::distinct('currency_code')->count('currency_code');

            $topRiskCountries = CountryRiskScore::with('country')
                ->orderByDesc('composite_score')
                ->limit(5)
                ->get();

            $topHighestRisk = CountryRiskScore::with('country')
                ->orderByDesc('composite_score')
                ->limit(10)
                ->get();

            $topLowestRisk = CountryRiskScore::with('country')
                ->orderBy('composite_score')
                ->limit(10)
                ->get();

            $recentChanges = CountryRiskScore::with('country')
                ->where('score_change', '!=', 0)
                ->orderByDesc('calculated_at')
                ->limit(10)
                ->get();

            $recentAlerts = ActivityLog::where('action', 'risk_alert')
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();

            // Format data for JSON response
            return response()->json([
                'status' => 'success',
                'data' => [
                    'avgRisk' => number_format($avgRisk, 1),
                    'countriesMonitored' => $countriesMonitored,
                    'extremeWeatherCount' => $extremeWeatherCount,
                    'currenciesCount' => $currenciesCount,
                    'topRiskCountries' => $topRiskCountries->map(function ($tr) {
                        return [
                            'country_name' => $tr->country->name ?? 'Unknown',
                            'country_flag' => $tr->country->flag_url ?? '',
                            'iso2' => $tr->country->iso2 ?? '',
                            'composite_score' => number_format($tr->composite_score, 1),
                            'risk_level' => ucfirst($tr->risk_level),
                            'score_change' => (float)$tr->score_change,
                        ];
                    }),
                    'topHighestRisk' => $topHighestRisk->map(function ($h) {
                        return [
                            'country_name' => $h->country->name ?? 'Unknown',
                            'country_flag' => $h->country->flag_url ?? '',
                            'iso2' => $h->country->iso2 ?? '',
                            'composite_score' => number_format($h->composite_score, 2),
                            'risk_level' => ucfirst($h->risk_level),
                        ];
                    }),
                    'topLowestRisk' => $topLowestRisk->map(function ($l) {
                        return [
                            'country_name' => $l->country->name ?? 'Unknown',
                            'country_flag' => $l->country->flag_url ?? '',
                            'iso2' => $l->country->iso2 ?? '',
                            'composite_score' => number_format($l->composite_score, 2),
                            'risk_level' => ucfirst($l->risk_level),
                        ];
                    }),
                    'recentChanges' => $recentChanges->map(function ($rc) {
                        return [
                            'country_name' => $rc->country->name ?? 'Unknown',
                            'country_flag' => $rc->country->flag_url ?? '',
                            'iso2' => $rc->country->iso2 ?? '',
                            'new_score' => number_format($rc->composite_score, 2),
                            'prev_score' => number_format($rc->composite_score - $rc->score_change, 2),
                            'change' => (float)$rc->score_change,
                        ];
                    }),
                    'recentAlerts' => $recentAlerts->map(function ($ra) {
                        return [
                            'description' => $ra->description,
                            'time_ago' => $ra->created_at->diffForHumans(),
                            'old_score' => number_format($ra->old_values['composite_score'] ?? 0, 2),
                            'new_score' => number_format($ra->new_values['composite_score'] ?? 0, 2),
                        ];
                    }),
                ],
                'sync_status' => 'online',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'sync_status' => 'offline',
            ], 500);
        }
    }

    /**
     * Get live Weather data for map and dashboard view.
     */
    public function weather()
    {
        try {
            $weatherEntries = WeatherData::with('country')
                ->select('weather_data.*')
                ->join(
                    \DB::raw('(SELECT MAX(id) as max_id FROM weather_data GROUP BY country_id) as latest'),
                    'weather_data.id', '=', 'latest.max_id'
                )
                ->get();

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
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'totalStations' => $weatherEntries->count(),
                    'extremeCount' => $weatherEntries->where('is_extreme', true)->count(),
                    'avgTemp' => round($weatherEntries->avg('temperature'), 1),
                    'avgHumidity' => round($weatherEntries->avg('humidity')),
                    'mapMarkers' => $mapMarkers,
                    'entries' => $weatherEntries->sortByDesc('is_extreme')->take(50)->map(function ($w) {
                        return [
                            'country_name' => $w->country->name ?? 'Unknown',
                            'country_flag' => $w->country->flag_url ?? '',
                            'iso2' => $w->country->iso2 ?? '',
                            'temperature' => $w->temperature,
                            'feels_like' => $w->feels_like,
                            'humidity' => $w->humidity,
                            'wind_speed' => $w->wind_speed,
                            'precipitation' => $w->precipitation,
                            'uv_index' => $w->uv_index,
                            'weather_description' => $w->weather_description,
                            'is_extreme' => $w->is_extreme,
                            'fetched_at' => $w->fetched_at ? $w->fetched_at->diffForHumans() : '—',
                        ];
                    }),
                ],
                'sync_status' => 'online',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'sync_status' => 'offline',
            ], 500);
        }
    }

    /**
     * Get live Exchange Rates data.
     */
    public function exchangeRates()
    {
        try {
            $latestRates = ExchangeRate::with('country')
                ->select('exchange_rates.*')
                ->join(
                    \DB::raw('(SELECT MAX(id) as max_id FROM exchange_rates GROUP BY country_id) as latest'),
                    'exchange_rates.id', '=', 'latest.max_id'
                )
                ->orderBy('currency_code')
                ->get();

            $topGainers = ExchangeRate::with('country')
                ->whereNotNull('change_percent')
                ->where('change_percent', '>', 0)
                ->orderByDesc('change_percent')
                ->limit(10)
                ->get();

            $topLosers = ExchangeRate::with('country')
                ->whereNotNull('change_percent')
                ->where('change_percent', '<', 0)
                ->orderBy('change_percent')
                ->limit(10)
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'totalCurrencies' => $latestRates->unique('currency_code')->count(),
                    'avgChange' => number_format($latestRates->avg('change_percent') ?? 0, 2) . '%',
                    'topGainers' => $topGainers->map(function ($g) {
                        return [
                            'currency_code' => $g->currency_code,
                            'country_flag' => $g->country->flag_url ?? '',
                            'change_percent' => number_format((float)$g->change_percent, 2),
                        ];
                    }),
                    'topLosers' => $topLosers->map(function ($l) {
                        return [
                            'currency_code' => $l->currency_code,
                            'country_flag' => $l->country->flag_url ?? '',
                            'change_percent' => number_format((float)$l->change_percent, 2),
                        ];
                    }),
                    'rates' => $latestRates->map(function ($r) {
                        return [
                            'currency_code' => $r->currency_code,
                            'currency_name' => $r->currency_name,
                            'country_name' => $r->country->name ?? '',
                            'country_flag' => $r->country->flag_url ?? '',
                            'rate_to_usd' => number_format((float)$r->rate_to_usd, 6),
                            'rate_to_idr' => $r->rate_to_idr ? number_format((float)$r->rate_to_idr, 2) : '—',
                            'change_percent' => $r->change_percent !== null ? number_format((float)$r->change_percent, 2) : null,
                            'rate_date' => $r->rate_date ? $r->rate_date->format('M d, Y') : '—',
                        ];
                    }),
                ],
                'sync_status' => 'online',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'sync_status' => 'offline',
            ], 500);
        }
    }

    /**
     * Get live news articles.
     */
    public function news()
    {
        try {
            $articles = NewsArticle::with('country')
                ->orderByDesc('published_at')
                ->limit(20)
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $articles->map(function ($a) {
                    return [
                        'title' => $a->title,
                        'description' => $a->description,
                        'source_name' => $a->source_name,
                        'source_url' => $a->source_url,
                        'image_url' => $a->image_url,
                        'sentiment' => $a->sentiment,
                        'country_name' => $a->country->name ?? null,
                        'country_flag' => $a->country->flag_url ?? null,
                        'published_at' => $a->published_at ? $a->published_at->diffForHumans() : '—',
                    ];
                }),
                'sync_status' => 'online',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'sync_status' => 'offline',
            ], 500);
        }
    }

    /**
     * Get live country risk and breakdown metrics for detail page.
     */
    public function countryRisk($code)
    {
        try {
            $code = strtoupper(trim($code));
            $country = Country::where('iso2', $code)->orWhere('iso3', $code)->first();
            if (!$country) {
                return response()->json(['status' => 'error', 'message' => 'Country not found'], 404);
            }

            // Recalculate score on demand to simulate true live updates
            try {
                app(\App\Services\Contracts\RiskScoringEngineInterface::class)->calculateCountryScore($country->id);
            } catch (\Throwable $e) {
                // If live API or calculation fails, we continue with cached risk scores
            }

            $score = CountryRiskScore::with(['details.riskCategory'])
                ->where('country_id', $country->id)
                ->orderByDesc('calculated_at')
                ->first();

            if (!$score) {
                return response()->json(['status' => 'error', 'message' => 'No risk score data'], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'composite_score' => number_format($score->composite_score, 2),
                    'risk_level' => ucfirst($score->risk_level),
                    'score_change' => (float)$score->score_change,
                    'calculated_at' => $score->calculated_at->diffForHumans(),
                    'details' => $score->details->map(function ($d) {
                        return [
                            'category_name' => $d->riskCategory->name ?? '',
                            'category_slug' => $d->riskCategory->slug ?? '',
                            'category_score' => (float)$d->category_score,
                            'weighted_score' => (float)$d->weighted_score,
                        ];
                    }),
                ],
                'sync_status' => 'online',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'sync_status' => 'offline',
            ], 500);
        }
    }
}
