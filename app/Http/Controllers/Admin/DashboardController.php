<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiLog;
use App\Models\RiskWeight;
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
        
        // API Calls in last 24h
        $apiCallsCount = ApiLog::where('called_at', '>=', now()->subDay())->count();
        $successfulCalls = ApiLog::where('called_at', '>=', now()->subDay())->where('is_success', true)->count();
        $successRate = $apiCallsCount > 0 ? round(($successfulCalls / $apiCallsCount) * 100, 1) : 100;
        
        // Avg Response Latency
        $avgLatency = round(ApiLog::where('called_at', '>=', now()->subDay())->avg('response_time') ?? 0);

        // Recent Audit Actions
        $recentActions = [
            [
                'type' => 'warning',
                'icon' => 'bi-pencil-square',
                'title' => 'Adjusted System Configurations',
                'description' => 'Key risk_score_high_max modified from 80.00 to 75.00.',
                'time' => 'Today, 11:20 AM',
                'ip' => '127.0.0.1'
            ],
            [
                'type' => 'success',
                'icon' => 'bi-person-check',
                'title' => 'Activated User Account',
                'description' => 'Admin verified and activated operator account procurement@gscrip.com.',
                'time' => 'Yesterday, 3:45 PM',
                'ip' => '127.0.0.1'
            ],
            [
                'type' => 'danger',
                'icon' => 'bi-trash',
                'title' => 'Purged Log History',
                'description' => 'Executed automatic database table cleanup for api_logs older than 3 months.',
                'time' => '28 Jun 2026, 12:00 AM',
                'ip' => 'System Command'
            ]
        ];

        return view('admin.dashboard', compact('weights', 'totalUsers', 'apiCallsCount', 'successRate', 'avgLatency', 'recentActions'));
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

        return redirect()->back()->with('success', 'Scoring weights updated successfully!');
    }
}
