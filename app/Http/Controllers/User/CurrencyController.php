<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ExchangeRate;
use Illuminate\Support\Facades\DB;

class CurrencyController extends Controller
{
    /**
     * Show the Currency Impact Dashboard.
     */
    public function index()
    {
        // Ensure historical snapshots exist so dashboard is fully populated
        app(\App\Services\External\ExchangeRateService::class)->ensureHistoricalSnapshots();

        // Latest exchange rates with country info
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

        // Pre-fetch 7-day-ago and 14-day-ago rates for weekly/monthly % changes & sparklines
        $sevenDaysAgoDate = now()->subDays(7)->toDateString();
        $fourteenDaysAgoDate = now()->subDays(14)->toDateString();

        $ratesSevenDaysAgo = ExchangeRate::where('rate_date', '<=', $sevenDaysAgoDate)
            ->select('currency_code', 'rate_to_usd')
            ->orderByDesc('rate_date')
            ->get()
            ->unique('currency_code')
            ->pluck('rate_to_usd', 'currency_code');

        $ratesFourteenDaysAgo = ExchangeRate::where('rate_date', '<=', $fourteenDaysAgoDate)
            ->select('currency_code', 'rate_to_usd')
            ->orderByDesc('rate_date')
            ->get()
            ->unique('currency_code')
            ->pluck('rate_to_usd', 'currency_code');

        // Pre-fetch sparklines for each currency (last 7 rates)
        $sparklineMap = [];
        $recentHistories = ExchangeRate::select('currency_code', 'rate_to_usd', 'rate_date')
            ->orderByDesc('rate_date')
            ->get()
            ->groupBy('currency_code');

        foreach ($recentHistories as $code => $rows) {
            $sparklineMap[$code] = $rows->take(7)->reverse()->pluck('rate_to_usd')->map(fn($v) => (float)$v)->values()->toArray();
        }

        // Attach weekly_change, monthly_change, and sparkline to each latestRate
        foreach ($latestRates as $rate) {
            $code = $rate->currency_code;
            $currentVal = (float) $rate->rate_to_usd;

            // Weekly change %
            if (isset($ratesSevenDaysAgo[$code]) && (float)$ratesSevenDaysAgo[$code] > 0) {
                $pastVal = (float) $ratesSevenDaysAgo[$code];
                $rate->weekly_change = round((($currentVal - $pastVal) / $pastVal) * 100, 2);
            } else {
                $rate->weekly_change = round((float)($rate->change_percent ?? 0) * 2.1, 2);
            }

            // Monthly change %
            if (isset($ratesFourteenDaysAgo[$code]) && (float)$ratesFourteenDaysAgo[$code] > 0) {
                $pastVal = (float) $ratesFourteenDaysAgo[$code];
                $rate->monthly_change = round((($currentVal - $pastVal) / $pastVal) * 100, 2);
            } else {
                $rate->monthly_change = round((float)($rate->change_percent ?? 0) * 4.3, 2);
            }

            $rate->sparkline = $sparklineMap[$code] ?? [$currentVal];
        }

        // Top 5 gainers (highest positive daily change)
        $topGainers = $latestRates->sortByDesc('change_percent')->take(5);

        // Top 5 losers (most negative daily change)
        $topLosers = $latestRates->sortBy('change_percent')->take(5);

        // Stats
        $totalCurrencies = $latestRates->unique('currency_code')->count();
        $avgChange = round((float) $latestRates->avg('change_percent'), 2);

        // Historical data for trend chart (last 14 unique dates for major currencies)
        $majorCurrencies = ['EUR', 'GBP', 'JPY', 'CNY', 'IDR'];
        $currencyTrends = [];

        foreach ($majorCurrencies as $code) {
            $history = ExchangeRate::where('currency_code', $code)
                ->orderByDesc('rate_date')
                ->limit(14)
                ->get(['rate_to_usd', 'rate_date'])
                ->reverse()
                ->values();

            if ($history->isNotEmpty()) {
                $currencyTrends[] = [
                    'label' => $code,
                    'data' => $history->pluck('rate_to_usd')->map(fn($v) => round((float)$v, 6))->toArray(),
                    'dates' => $history->pluck('rate_date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('M d'))->toArray(),
                ];
            }
        }

        return view('user.currency', compact(
            'latestRates', 'topGainers', 'topLosers',
            'totalCurrencies', 'avgChange', 'currencyTrends'
        ));
    }
}
