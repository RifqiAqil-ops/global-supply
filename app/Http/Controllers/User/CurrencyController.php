<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\ExchangeRate;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    /**
     * Show the Currency Impact Dashboard.
     */
    public function index()
    {
        // Latest exchange rates with country info
        $latestRates = ExchangeRate::with('country')
            ->select('exchange_rates.*')
            ->join(
                \DB::raw('(SELECT MAX(id) as max_id FROM exchange_rates GROUP BY country_id) as latest'),
                'exchange_rates.id', '=', 'latest.max_id'
            )
            ->orderBy('currency_code')
            ->get();

        // Top 10 gainers (highest positive daily change)
        $topGainers = ExchangeRate::with('country')
            ->whereNotNull('change_percent')
            ->where('change_percent', '>', 0)
            ->orderByDesc('change_percent')
            ->limit(10)
            ->get();

        // Top 10 losers (most negative daily change)
        $topLosers = ExchangeRate::with('country')
            ->whereNotNull('change_percent')
            ->where('change_percent', '<', 0)
            ->orderBy('change_percent')
            ->limit(10)
            ->get();

        // Stats
        $totalCurrencies = $latestRates->unique('currency_code')->count();
        $avgChange = $latestRates->avg('change_percent') ?? 0;

        // Historical data for chart (last 7 unique dates for major currencies)
        $majorCurrencies = ['EUR', 'GBP', 'JPY', 'CNY', 'IDR'];
        $currencyTrends = [];
        foreach ($majorCurrencies as $code) {
            $history = ExchangeRate::where('currency_code', $code)
                ->orderByDesc('rate_date')
                ->limit(30)
                ->get(['rate_to_usd', 'rate_date'])
                ->reverse()
                ->values();

            if ($history->isNotEmpty()) {
                $currencyTrends[] = [
                    'label' => $code,
                    'data' => $history->pluck('rate_to_usd')->map(fn($v) => round((float)$v, 6))->toArray(),
                    'dates' => $history->pluck('rate_date')->map(fn($d) => $d->format('M d'))->toArray(),
                ];
            }
        }

        return view('user.currency', compact(
            'latestRates', 'topGainers', 'topLosers',
            'totalCurrencies', 'avgChange', 'currencyTrends'
        ));
    }
}
