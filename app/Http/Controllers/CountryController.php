<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Services\External\GNewsService;
use App\Services\External\OpenMeteoService;
use App\Services\External\RestCountriesService;
use App\Services\External\ExchangeRateService;
use App\Services\External\WorldBankService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class CountryController extends Controller
{
    protected RestCountriesService $countryService;
    protected WorldBankService $worldBankService;
    protected OpenMeteoService $openMeteoService;
    protected ExchangeRateService $exchangeRateService;
    protected GNewsService $gnewsService;

    public function __construct(
        RestCountriesService $countryService,
        WorldBankService $worldBankService,
        OpenMeteoService $openMeteoService,
        ExchangeRateService $exchangeRateService,
        GNewsService $gnewsService
    ) {
        $this->countryService = $countryService;
        $this->worldBankService = $worldBankService;
        $this->openMeteoService = $openMeteoService;
        $this->exchangeRateService = $exchangeRateService;
        $this->gnewsService = $gnewsService;
    }

    /**
     * Show live Country Detail page.
     */
    public function show($code)
    {
        $code = strtoupper(trim($code));
        
        // Find country first in DB to get the ID
        $countryModel = Country::where('iso2', $code)->orWhere('iso3', $code)->first();
        if (!$countryModel) {
            abort(404, "Country '{$code}' not found.");
        }

        $isOffline = false;

        // 1. Fetch World Bank indicators live (to sync population data to master record first)
        try {
            $indicators = $this->worldBankService->getLatestIndicators($countryModel->id);
            if ($indicators->first() && $indicators->first()->isCached) {
                $isOffline = true;
            }
        } catch (Throwable $e) {
            Log::warning("Country details failed to load live indicators: " . $e->getMessage());
            $indicators = collect();
            $isOffline = true;
        }

        // 2. Fetch REST Countries live data (reads updated population from DB)
        try {
            $countryDTO = $this->countryService->fetchByIso($code);
            if ($countryDTO->isCached) {
                $isOffline = true;
            }
        } catch (Throwable $e) {
            Log::error("Failed to load live country details: " . $e->getMessage());
            abort(500, "Unable to load country details.");
        }

        // 3. Fetch Weather live
        try {
            $weather = $this->openMeteoService->getLatestWeather($countryModel->id);
            if ($weather && $weather->isCached) {
                $isOffline = true;
            }
        } catch (Throwable $e) {
            Log::warning("Country details failed to load live weather: " . $e->getMessage());
            $weather = null;
            $isOffline = true;
        }

        // 4. Fetch Exchange Rate live
        try {
            $exchangeRate = $countryDTO->currencyCode 
                ? $this->exchangeRateService->getLatestRate($countryDTO->currencyCode)
                : null;
            if ($exchangeRate && $exchangeRate->isCached) {
                $isOffline = true;
            }
        } catch (Throwable $e) {
            Log::warning("Country details failed to load live exchange rate: " . $e->getMessage());
            $exchangeRate = null;
            $isOffline = true;
        }

        // 5. Fetch News live
        try {
            $news = $this->gnewsService->getCountryNews($countryModel->id);
            if ($news->first() && $news->first()->isCached) {
                $isOffline = true;
            }
        } catch (Throwable $e) {
            Log::warning("Country details failed to load live news: " . $e->getMessage());
            $news = collect();
            $isOffline = true;
        }

        return view('user.country_detail', compact(
            'countryModel',
            'countryDTO',
            'indicators',
            'weather',
            'exchangeRate',
            'news',
            'isOffline'
        ));
    }
}
