<?php

namespace App\Services\External;

use App\DTOs\ExchangeRateDTO;
use App\Http\Clients\BaseApiClient;
use App\Models\Country;
use App\Models\ExchangeRate;
use App\Repositories\Contracts\ExchangeRateRepositoryInterface;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class ExchangeRateService extends BaseApiClient
{
    protected ExchangeRateRepositoryInterface $rateRepository;

    public function __construct(ExchangeRateRepositoryInterface $rateRepository)
    {
        parent::__construct(
            config('gscrip.api.exchange_rate', 'https://api.exchangerate-api.com/v4'),
            'ExchangeRate API'
        );
        $this->rateRepository = $rateRepository;
    }

    /**
     * Synchronize exchange rates for all countries that have a currency code.
     *
     * Strategy: Fetch the full USD rates list ONCE, then map to our country
     * currency codes — avoiding redundant API calls entirely.
     *
     * @return array Sync summary: ['currencies_processed' => X, 'new' => Y, 'updated' => Z, 'failed' => W]
     */
    public function syncAllRates(): array
    {
        $summary = [
            'currencies_processed' => 0,
            'new' => 0,
            'updated' => 0,
            'failed' => 0,
        ];

        // Fetch entire USD rate list in a single request
        try {
            $response = $this->request('GET', 'latest/USD');

            if (empty($response) || !isset($response['rates'])) {
                Log::warning("ExchangeRate API returned an empty or malformed response.");
                return $summary;
            }

            $rates = $response['rates'];           // ['IDR' => 16245.5, 'EUR' => 0.92, ...]
            $rateDate = $response['date'] ?? now()->toDateString();
            $idrRate = $rates['IDR'] ?? null;      // Indonesian Rupiah rate (reference for rate_to_idr)

        } catch (Throwable $e) {
            Log::error("ExchangeRate API bulk fetch failed: " . $e->getMessage());
            throw $e;
        }

        // Retrieve unique currency codes used across all countries
        $currencies = Country::whereNotNull('currency_code')
            ->distinct()
            ->pluck('currency_code', 'id')         // country_id => currency_code
            ->toArray();

        if (empty($currencies)) {
            Log::warning("No currency codes found in countries table.");
            return $summary;
        }

        // Pre-load the latest rates before $rateDate for change_percent computation (keyed by currency_code)
        $previousRates = ExchangeRate::select('exchange_rates.currency_code', 'exchange_rates.rate_to_usd')
            ->join(\DB::raw('(SELECT currency_code, MAX(rate_date) as max_date FROM exchange_rates WHERE rate_date < \'' . $rateDate . '\' GROUP BY currency_code) as prev_latest'), function($join) {
                $join->on('exchange_rates.currency_code', '=', 'prev_latest.currency_code')
                     ->on('exchange_rates.rate_date', '=', 'prev_latest.max_date');
            })
            ->pluck('rate_to_usd', 'currency_code')
            ->toArray();

        // Build a unique set of currency codes to process (avoid duplicate processing)
        $processedCodes = [];

        foreach ($currencies as $countryId => $currencyCode) {
            $currencyCode = strtoupper($currencyCode);

            // Skip if not found in API response
            if (!isset($rates[$currencyCode])) {
                $summary['failed']++;
                continue;
            }

            try {
                $apiRate = (float) $rates[$currencyCode];  // e.g., 16245.50 means 1 USD = 16245.50 IDR

                // Convert: rate_to_usd means "1 unit of this currency = X USD"
                $rateToUsd = $apiRate > 0 ? round(1 / $apiRate, 10) : 0.0;

                // rate_to_idr: "1 unit of this currency = X IDR"
                $rateToIdr = ($idrRate && $apiRate > 0)
                    ? round($idrRate / $apiRate, 4)
                    : null;

                // Change percent compared to previous day
                $changePercent = null;
                if (isset($previousRates[$currencyCode]) && (float)$previousRates[$currencyCode] > 0) {
                    $prevRateToUsd = (float)$previousRates[$currencyCode];
                    $changePercent = round((($rateToUsd - $prevRateToUsd) / $prevRateToUsd) * 100, 4);
                }

                // Get currency name from the countries table
                $currencyName = Country::where('currency_code', $currencyCode)
                    ->value('currency_name');

                // updateOrCreate based on unique [currency_code, rate_date] constraint
                $existing = ExchangeRate::where('currency_code', $currencyCode)
                    ->where('rate_date', $rateDate)
                    ->first();

                $payload = [
                    'currency_code' => $currencyCode,
                    'currency_name' => $currencyName,
                    'rate_to_usd'   => $rateToUsd,
                    'rate_to_idr'   => $rateToIdr,
                    'change_percent' => $changePercent,
                    'rate_date'     => $rateDate,
                    'source'        => 'ExchangeRate API',
                ];

                if ($existing) {
                    $existing->update($payload);
                    $summary['updated']++;
                } else {
                    ExchangeRate::create($payload);
                    $summary['new']++;
                }

                // Link country_id if not already tracked
                if (!in_array($currencyCode, $processedCodes)) {
                    // Update the first country record linked to this currency with country_id
                    ExchangeRate::where('currency_code', $currencyCode)
                        ->where('rate_date', $rateDate)
                        ->whereNull('country_id')
                        ->update(['country_id' => $countryId]);

                    $processedCodes[] = $currencyCode;
                    $summary['currencies_processed']++;
                }

                // Invalidate cache for this currency
                Cache::forget("exchange_rate.{$currencyCode}.latest");

            } catch (Throwable $e) {
                Log::error("Failed to sync exchange rate for '{$currencyCode}': " . $e->getMessage());
                $summary['failed']++;
            }
        }

        return $summary;
    }

    /**
     * Get the latest rate for a currency code. Always attempts real-time fetch first, falls back to DB cache if API fails.
     */
    public function getLatestRate(string $currencyCode, bool $forceRefresh = false): ?ExchangeRate
    {
        $currencyCode = strtoupper($currencyCode);
        $cacheKey = "exchange_rate.{$currencyCode}.latest";
        $ttl = config('gscrip.cache_ttl.exchange_rate', 3600); // 1 hour

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        // Try API first
        try {
            // Find a country with this currency code to link country_id if possible
            $country = Country::where('currency_code', $currencyCode)->first();
            
            $response = $this->request('GET', 'latest/USD');
            if (empty($response) || !isset($response['rates'])) {
                throw new \Exception("ExchangeRate API returned invalid response.");
            }

            $rates = $response['rates'];
            $rateDate = $response['date'] ?? now()->toDateString();
            $idrRate = $rates['IDR'] ?? null;

            if (!isset($rates[$currencyCode])) {
                throw new \Exception("Currency code '{$currencyCode}' not found in API response.");
            }

            $apiRate = (float) $rates[$currencyCode];
            $rateToUsd = $apiRate > 0 ? round(1 / $apiRate, 10) : 0.0;
            $rateToIdr = ($idrRate && $apiRate > 0) ? round($idrRate / $apiRate, 4) : null;

            $prevRate = ExchangeRate::where('currency_code', $currencyCode)
                ->where('rate_date', '<', $rateDate)
                ->orderByDesc('rate_date')
                ->first();
            $changePercent = null;
            if ($prevRate && (float)$prevRate->rate_to_usd > 0) {
                $changePercent = round((($rateToUsd - (float)$prevRate->rate_to_usd) / (float)$prevRate->rate_to_usd) * 100, 4);
            }

            $record = ExchangeRate::updateOrCreate(
                ['currency_code' => $currencyCode, 'rate_date' => $rateDate],
                [
                    'country_id'     => $country ? $country->id : null,
                    'currency_name'  => $country ? $country->currency_name : null,
                    'rate_to_usd'    => $rateToUsd,
                    'rate_to_idr'    => $rateToIdr,
                    'change_percent' => $changePercent,
                    'source'         => 'ExchangeRate API',
                ]
            );

            if ($record) {
                $record->isCached = false;
            }
            Cache::put($cacheKey, $record, $ttl);
            return $record;

        } catch (Throwable $e) {
            Log::warning("ExchangeRate API call failed for '{$currencyCode}', falling back to database: " . $e->getMessage());

            // Fallback to cache/database
            $record = Cache::remember($cacheKey, $ttl, function () use ($currencyCode) {
                return $this->rateRepository->latestRate($currencyCode);
            });

            if ($record) {
                $record->isCached = true;
            }

            return $record;
        }
    }

    /**
     * Get historical rates for a currency code.
     */
    public function getHistoricalRates(string $currencyCode, int $limit = 30): EloquentCollection
    {
        return $this->rateRepository->historicalRates($currencyCode, $limit);
    }

    /**
     * Force refresh a single country's exchange rate.
     */
    public function refreshCountryRate(int $countryId): ExchangeRate
    {
        $country = Country::findOrFail($countryId);

        if (!$country->currency_code) {
            throw new \Exception("Country '{$country->name}' does not have a currency code.");
        }

        $response = $this->request('GET', 'latest/USD');

        if (empty($response) || !isset($response['rates'])) {
            throw new \Exception("ExchangeRate API returned an invalid response.");
        }

        $rates = $response['rates'];
        $rateDate = $response['date'] ?? now()->toDateString();
        $currencyCode = strtoupper($country->currency_code);

        if (!isset($rates[$currencyCode])) {
            throw new \Exception("Currency code '{$currencyCode}' not found in API response.");
        }

        $apiRate = (float) $rates[$currencyCode];
        $idrRate = $rates['IDR'] ?? null;

        $rateToUsd = $apiRate > 0 ? round(1 / $apiRate, 10) : 0.0;
        $rateToIdr = ($idrRate && $apiRate > 0) ? round($idrRate / $apiRate, 4) : null;

        $prevRate = ExchangeRate::where('currency_code', $currencyCode)
            ->where('rate_date', '<', $rateDate)
            ->orderByDesc('rate_date')
            ->first();
        $changePercent = null;
        if ($prevRate && (float)$prevRate->rate_to_usd > 0) {
            $changePercent = round((($rateToUsd - (float)$prevRate->rate_to_usd) / (float)$prevRate->rate_to_usd) * 100, 4);
        }

        $record = ExchangeRate::updateOrCreate(
            ['currency_code' => $currencyCode, 'rate_date' => $rateDate],
            [
                'country_id'     => $countryId,
                'currency_name'  => $country->currency_name,
                'rate_to_usd'    => $rateToUsd,
                'rate_to_idr'    => $rateToIdr,
                'change_percent' => $changePercent,
                'source'         => 'ExchangeRate API',
            ]
        );

        Cache::forget("exchange_rate.{$currencyCode}.latest");

        return $record;
    }

    /**
     * Flush all exchange rate cache.
     */
    public function flushCache(): void
    {
        Cache::flush();
    }
}
