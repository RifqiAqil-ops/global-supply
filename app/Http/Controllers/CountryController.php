<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\EconomicIndicator;
use App\Models\ApiLog;
use App\Services\External\GNewsService;
use App\Services\External\OpenMeteoService;
use App\Services\External\RestCountriesService;
use App\Services\External\ExchangeRateService;
use App\Services\External\WorldBankService;
use App\Repositories\Contracts\CountryRepositoryInterface;
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
    protected CountryRepositoryInterface $countryRepository;

    public function __construct(
        RestCountriesService $countryService,
        WorldBankService $worldBankService,
        OpenMeteoService $openMeteoService,
        ExchangeRateService $exchangeRateService,
        GNewsService $gnewsService,
        CountryRepositoryInterface $countryRepository
    ) {
        $this->countryService = $countryService;
        $this->worldBankService = $worldBankService;
        $this->openMeteoService = $openMeteoService;
        $this->exchangeRateService = $exchangeRateService;
        $this->gnewsService = $gnewsService;
        $this->countryRepository = $countryRepository;
    }

    /**
     * Show Global Country Dashboard.
     */
    public function index(Request $request)
    {
        $filters = $request->only([
            'search', 'region', 'population_range', 'gdp_range', 'inflation_range', 'sort_by', 'sort_dir'
        ]);

        // Default pagination limit = 15
        $countries = $this->countryRepository->paginateFiltered(15, $filters);
        
        // Eager load relations for N+1 optimization
        $countries->load(['latestWeather', 'economicIndicators', 'latestRiskScore']);

        // 1. Total countries
        $totalCountries = Country::count();

        // 2. Average GDP (Latest available per country)
        $avgGdp = EconomicIndicator::where('indicator_code', 'NY.GDP.MKTP.CD')
            ->whereRaw('year = (select max(year) from economic_indicators as sub where sub.country_id = economic_indicators.country_id and sub.indicator_code = economic_indicators.indicator_code)')
            ->avg('value') ?? 0.0;

        // 3. Average Inflation (Latest available per country)
        $avgInflation = EconomicIndicator::where('indicator_code', 'FP.CPI.TOTL.ZG')
            ->whereRaw('year = (select max(year) from economic_indicators as sub where sub.country_id = economic_indicators.country_id and sub.indicator_code = economic_indicators.indicator_code)')
            ->avg('value') ?? 0.0;

        // 4. Average Population
        $avgPopulation = Country::avg('population') ?? 0.0;

        // 5. Last Live Update (Latest successful API log)
        $lastLiveUpdate = ApiLog::where('is_success', true)
            ->latest()
            ->value('created_at');

        return view('user.countries_index', compact(
            'countries', 'filters', 'totalCountries', 'avgGdp', 'avgInflation', 'avgPopulation', 'lastLiveUpdate'
        ));
    }

    /**
     * Show live Country Detail page.
     */
    public function show($code)
    {
        $code = strtoupper(trim($code));
        
        // Find country first in DB to get the ID with ports relation eager loaded
        $countryModel = Country::with(['ports'])->where('iso2', $code)->orWhere('iso3', $code)->first();
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

        // 6. Recalculate latest risk score from current API feeds
        try {
            app(\App\Services\Contracts\RiskScoringEngineInterface::class)->calculateCountryScore($countryModel->id);
            $countryModel->load(['latestRiskScore.details.riskCategory']);
        } catch (\Throwable $e) {
            Log::warning("Failed to calculate country risk score on load: " . $e->getMessage());
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
