<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RiskWeight;
use App\Services\Contracts\RiskScoringEngineInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class DashboardController extends Controller
{
    /**
     * Show the Admin Control Console.
     */
    public function index()
    {
        $weights = RiskWeight::with('riskCategory')->get();
        
        // Sum of registered users
        $totalUsers = \App\Models\User::count();

        // Fetch recent risk alerts dynamically
        $recentAlerts = \App\Models\ActivityLog::where('action', 'risk_alert')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('weights', 'totalUsers', 'recentAlerts'));
    }

    /**
     * Diagnose all 5 external API services.
     */
    public function diagnoseApi()
    {
        $services = [
            [
                'name' => 'REST Countries',
                'url' => config('gscrip.api.rest_countries', 'https://raw.githubusercontent.com/mledoze/countries/master') . '/countries.json',
                'endpoint' => '/countries.json'
            ],
            [
                'name' => 'World Bank API',
                'url' => config('gscrip.api.world_bank', 'https://api.worldbank.org/v2') . '/country/ID?format=json',
                'endpoint' => '/country/{code}'
            ],
            [
                'name' => 'Open-Meteo',
                'url' => config('gscrip.api.open_meteo', 'https://api.open-meteo.com/v1') . '/forecast?latitude=0&longitude=0&current=temperature_2m',
                'endpoint' => '/forecast'
            ],
            [
                'name' => 'ExchangeRate API',
                'url' => config('gscrip.api.exchange_rate', 'https://api.exchangerate-api.com/v4') . '/latest/USD',
                'endpoint' => '/latest/{base}'
            ],
            [
                'name' => 'GNews API',
                'url' => config('gscrip.api.gnews.base_url', 'https://gnews.io/api/v4') . '/search?q=test&token=' . (config('gscrip.api.gnews.key') ?: 'bad_key'),
                'endpoint' => '/search'
            ],
        ];

        $results = [];

        foreach ($services as $service) {
            $t1 = microtime(true);
            $statusCode = 0;
            $statusText = 'Inactive';
            $statusType = 'danger';

            try {
                $response = Http::timeout(4)->get($service['url']);
                $statusCode = $response->status();
                $latency = round((microtime(true) - $t1) * 1000);

                if ($statusCode === 200) {
                    $statusText = 'Active (200)';
                    $statusType = 'success';
                } elseif ($statusCode === 401) {
                    $statusText = 'Unauthorized (401)';
                    $statusType = 'warning';
                } elseif ($statusCode === 403) {
                    $statusText = 'Forbidden (403)';
                    $statusType = 'warning';
                } elseif ($statusCode === 429) {
                    $statusText = 'Rate Limited (429)';
                    $statusType = 'warning';
                } else {
                    $statusText = 'Error (' . $statusCode . ')';
                    $statusType = 'danger';
                }

                // If GNews check has no key and returned 401, it is functionally operational but unauthorized (normal fallback)
                if ($service['name'] === 'GNews API' && empty(config('gscrip.api.gnews.key')) && $statusCode === 401) {
                    $statusText = 'No Key Configured (401)';
                    $statusType = 'warning';
                }

            } catch (Throwable $e) {
                $latency = round((microtime(true) - $t1) * 1000);
                $statusText = 'Connection Timeout';
                $statusType = 'danger';
                $statusCode = 504;
            }

            $results[] = [
                'name' => $service['name'],
                'endpoint' => $service['endpoint'],
                'latency' => $latency . 'ms',
                'status_code' => $statusCode,
                'status_text' => $statusText,
                'status_type' => $statusType,
                'last_checked' => now()->format('h:i A')
            ];
        }

        return response()->json($results);
    }

    /**
     * Edit scoring weights (update database values).
     */
    public function updateWeights(Request $request)
    {
        $weights = $request->input('weights', []); // category_id => weight_percent
        
        $total = 0;
        foreach ($weights as $id => $val) {
            $total += (float) $val;
        }

        // Validate total weights sum equals 100%
        if (abs($total - 100.0) > 0.001) {
            return redirect()->back()
                ->with('error', 'The sum of all scoring weights must equal exactly 100%. Current sum: ' . $total . '%');
        }

        // Update each weight
        foreach ($weights as $id => $val) {
            $weightModel = RiskWeight::where('risk_category_id', $id)->first();
            if ($weightModel) {
                $weightModel->update([
                    'weight' => round($val / 100.0, 4)
                ]);
            }
        }

        // Recalculate risk scores for all countries immediately after weight updates
        try {
            app(RiskScoringEngineInterface::class)->recalculateAllCountries();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Failed to recalculate risk scores after weight update: " . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Scoring weights updated successfully!');
    }

    /**
     * Show the Risk Weights adjustment console.
     */
    public function weights()
    {
        $weights = RiskWeight::with('riskCategory')->get();
        return view('admin.weights.index', compact('weights'));
    }

    /**
     * Show the User Manager database list.
     */
    public function users()
    {
        $users = \App\Models\User::orderBy('name')->paginate(10);
        return view('admin.users_index', compact('users'));
    }
}
