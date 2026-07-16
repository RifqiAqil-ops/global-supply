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
        // Latest exchange rates with country info (grouped by currency_code to prevent collapsing null country_id)
        $latestRates = ExchangeRate::with('country')
            ->select('exchange_rates.*')
            ->join(
                \DB::raw('(SELECT currency_code, MAX(rate_date) as max_date FROM exchange_rates GROUP BY currency_code) as latest'),
                function($join) {
                    $join->on('exchange_rates.currency_code', '=', 'latest.currency_code')
                         ->on('exchange_rates.rate_date', '=', 'latest.max_date');
                }
            )
            ->orderBy('currency_code')
            ->get();

        // Filter out currencies with null change_percent for stats and movers
        $ratesWithChange = $latestRates->whereNotNull('change_percent');

        // Top 5 gainers (highest positive daily change)
        $topGainers = $ratesWithChange->where('change_percent', '>', 0)
            ->sortByDesc('change_percent')
            ->take(5);

        // Top 5 losers (most negative daily change)
        $topLosers = $ratesWithChange->where('change_percent', '<', 0)
            ->sortBy('change_percent')
            ->take(5);

        // Stats
        $totalCurrencies = $latestRates->unique('currency_code')->count();
        $avgChange = $ratesWithChange->isEmpty() ? null : (float)$ratesWithChange->avg('change_percent');

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
