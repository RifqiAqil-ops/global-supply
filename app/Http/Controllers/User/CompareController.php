<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\CountryRiskScore;
use App\Models\EconomicIndicator;
use App\Models\ExchangeRate;
use Illuminate\Http\Request;

class CompareController extends Controller
{
    /**
     * Show the Country Comparison page.
     */
    public function index(Request $request)
    {
        // All countries for the selector dropdown
        $allCountries = Country::orderBy('name')->get(['id', 'iso2', 'iso3', 'name', 'flag_url']);

        // Selected country codes (comma-separated or array)
        $selectedCodes = $request->input('countries', []);
        if (is_string($selectedCodes)) {
            $selectedCodes = array_filter(explode(',', $selectedCodes));
        }

        $comparisonData = collect();

        if (count($selectedCodes) >= 2) {
            $countries = Country::with([
                'latestRiskScore.details.riskCategory',
                'latestWeather',
            ])
            ->whereIn('iso2', $selectedCodes)
            ->get();

            foreach ($countries as $country) {
                // Get latest economic indicators
                $gdp = EconomicIndicator::where('country_id', $country->id)
                    ->where('indicator_code', 'NY.GDP.MKTP.CD')
                    ->orderByDesc('year')->first();
                $gdpPerCapita = EconomicIndicator::where('country_id', $country->id)
                    ->where('indicator_code', 'NY.GDP.PCAP.CD')
                    ->orderByDesc('year')->first();
                $inflation = EconomicIndicator::where('country_id', $country->id)
                    ->where('indicator_code', 'FP.CPI.TOTL.ZG')
                    ->orderByDesc('year')->first();

                // Get latest exchange rate
                $exchangeRate = ExchangeRate::where('country_id', $country->id)
                    ->orderByDesc('rate_date')->first();

                $comparisonData->push([
                    'country' => $country,
                    'gdp' => $gdp?->value,
                    'gdp_year' => $gdp?->year,
                    'gdp_per_capita' => $gdpPerCapita?->value,
                    'inflation' => $inflation?->value,
                    'inflation_year' => $inflation?->year,
                    'population' => $country->population,
                    'weather' => $country->latestWeather,
                    'exchange_rate' => $exchangeRate,
                    'risk_score' => $country->latestRiskScore,
                ]);
            }
        }

        // Chart data: GDP trend for selected countries
        $gdpTrends = [];
        $inflationTrends = [];
        if (count($selectedCodes) >= 2) {
            foreach ($comparisonData as $item) {
                $countryId = $item['country']->id;
                $countryName = $item['country']->name;

                $gdpHistory = EconomicIndicator::where('country_id', $countryId)
                    ->where('indicator_code', 'NY.GDP.MKTP.CD')
                    ->orderBy('year')
                    ->get(['year', 'value']);

                $gdpTrends[] = [
                    'label' => $countryName,
                    'data' => $gdpHistory->pluck('value')->map(fn($v) => round((float)$v / 1e9, 2))->toArray(),
                    'years' => $gdpHistory->pluck('year')->toArray(),
                ];

                $inflationHistory = EconomicIndicator::where('country_id', $countryId)
                    ->where('indicator_code', 'FP.CPI.TOTL.ZG')
                    ->orderBy('year')
                    ->get(['year', 'value']);

                $inflationTrends[] = [
                    'label' => $countryName,
                    'data' => $inflationHistory->pluck('value')->map(fn($v) => round((float)$v, 2))->toArray(),
                    'years' => $inflationHistory->pluck('year')->toArray(),
                ];
            }
        }

        return view('user.compare', compact(
            'allCountries', 'selectedCodes', 'comparisonData',
            'gdpTrends', 'inflationTrends'
        ));
    }
}
