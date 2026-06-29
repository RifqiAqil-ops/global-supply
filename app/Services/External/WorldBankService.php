<?php

namespace App\Services\External;

use App\DTOs\EconomicIndicatorDTO;
use App\Http\Clients\BaseApiClient;
use App\Models\Country;
use App\Models\EconomicIndicator;
use App\Repositories\Contracts\EconomicIndicatorRepositoryInterface;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class WorldBankService extends BaseApiClient
{
    protected EconomicIndicatorRepositoryInterface $indicatorRepository;

    /**
     * Official World Bank Indicator Codes.
     */
    public const INDICATORS = [
        'NY.GDP.MKTP.CD' => 'GDP (current USD)',
        'NY.GDP.PCAP.CD' => 'GDP per capita (current USD)',
        'FP.CPI.TOTL.ZG' => 'Inflation, consumer prices (annual %)',
        'SP.POP.TOTL'    => 'Population, total',
        'NE.EXP.GNFS.CD' => 'Exports of goods and services (current USD)',
        'NE.IMP.GNFS.CD' => 'Imports of goods and services (current USD)',
    ];

    public function __construct(EconomicIndicatorRepositoryInterface $indicatorRepository)
    {
        parent::__construct(
            config('gscrip.api.world_bank', 'https://api.worldbank.org/v2'),
            'World Bank'
        );
        $this->indicatorRepository = $indicatorRepository;
    }

    /**
     * Synchronize and cache economic indicators from the World Bank API.
     *
     * @param int $startYear Start year for historical window
     * @param int $endYear End year for historical window
     * @return array Sync summary counts: ['countries_processed' => X, 'new' => Y, 'updated' => Z, 'failed' => W]
     */
    public function syncEconomicIndicators(int $startYear = 2020, int $endYear = 2025): array
    {
        $summary = [
            'countries_processed' => 0,
            'new' => 0,
            'updated' => 0,
            'failed' => 0
        ];

        $countries = Country::all();
        $summary['countries_processed'] = $countries->count();

        // Pre-cache country database lookups to optimize database hits during sync loops
        $countryMap = $countries->pluck('id', 'iso3')->toArray();

        // Tracker for country population updates (stores latest year populated to prevent back-updating)
        $latestPopTracker = [];

        foreach (self::INDICATORS as $code => $name) {
            try {
                $endpoint = "country/all/indicator/{$code}";
                $params = [
                    'query' => [
                        'date' => "{$startYear}:{$endYear}",
                        'format' => 'json',
                        'per_page' => 2000
                    ]
                ];

                Log::info("Fetching World Bank indicator '{$code}' ({$name}) for years {$startYear}:{$endYear}...");
                $response = $this->request('GET', $endpoint, $params);

                // World Bank API returns an array: [0 => metadata, 1 => records array]
                if (empty($response) || !isset($response[1]) || !is_array($response[1])) {
                    Log::warning("No records returned from World Bank API for indicator {$code}.");
                    $summary['failed']++;
                    continue;
                }

                $records = $response[1];

                foreach ($records as $record) {
                    try {
                        $iso3 = strtoupper($record['countryiso3code'] ?? '');
                        if (empty($iso3) || !isset($countryMap[$iso3])) {
                            continue; // Skip aggregate regions or unmapped countries
                        }

                        $countryId = $countryMap[$iso3];
                        $year = (int) ($record['date'] ?? 0);
                        $value = $record['value'] !== null ? (float) $record['value'] : null;

                        if ($year === 0 || $value === null) {
                            continue; // Skip null data points
                        }

                        // Save indicator record
                        $existing = EconomicIndicator::where('country_id', $countryId)
                            ->where('indicator_code', $code)
                            ->where('year', $year)
                            ->first();

                        if ($existing) {
                            $existing->update([
                                'value' => $value,
                                'unit' => $record['unit'] ?: null,
                                'source' => 'World Bank API',
                            ]);
                            $summary['updated']++;
                        } else {
                            EconomicIndicator::create([
                                'country_id' => $countryId,
                                'indicator_code' => $code,
                                'indicator_name' => $name,
                                'year' => $year,
                                'value' => $value,
                                'unit' => $record['unit'] ?: null,
                                'source' => 'World Bank API',
                            ]);
                            $summary['new']++;
                        }

                        // Side Effect: If the indicator is total population, update the master `countries` table population
                        if ($code === 'SP.POP.TOTL') {
                            $trackedLatest = $latestPopTracker[$countryId] ?? 0;
                            if ($year > $trackedLatest) {
                                Country::where('id', $countryId)->update(['population' => (int) $value]);
                                $latestPopTracker[$countryId] = $year;
                            }
                        }

                    } catch (Throwable $e) {
                        Log::error("Failed to parse indicator record: " . $e->getMessage(), ['record' => $record]);
                        $summary['failed']++;
                    }
                }

            } catch (Throwable $e) {
                Log::error("Error sync indicator '{$code}': " . $e->getMessage());
                $summary['failed']++;
            }
        }

        // Clear general query cache
        $this->flushCache();

        return $summary;
    }

    /**
     * Get indicators for a country. Always attempts real-time fetch first, falls back to DB cache if API fails.
     */
    public function getLatestIndicators(int $countryId, bool $forceRefresh = false): EloquentCollection
    {
        $country = Country::findOrFail($countryId);
        $cacheKey = "country.{$countryId}.indicators.latest";

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        // Try live parallel API fetch first
        try {
            $startYear = now()->subYears(5)->year;
            $endYear = now()->year;

            $this->refreshCountryIndicators($country, $startYear, $endYear);

            $indicators = $this->indicatorRepository->latestIndicators($countryId);
            foreach ($indicators as $ind) {
                $ind->isCached = false;
            }

            Cache::put($cacheKey, $indicators, config('gscrip.cache_ttl.world_bank', 86400));
            return $indicators;

        } catch (Throwable $e) {
            Log::warning("World Bank API call failed for '{$country->name}', falling back to database: " . $e->getMessage());

            // Fallback to cache/database
            $indicators = Cache::remember($cacheKey, config('gscrip.cache_ttl.world_bank', 86400), function () use ($countryId) {
                return $this->indicatorRepository->latestIndicators($countryId);
            });

            foreach ($indicators as $ind) {
                $ind->isCached = true;
            }

            return $indicators;
        }
    }

    /**
     * Refreshes indicators for a single country using Laravel's parallel HTTP pool.
     */
    public function refreshCountryIndicators(Country $country, int $startYear, int $endYear): void
    {
        $iso3 = strtoupper($country->iso3);
        $baseUrl = config('gscrip.api.world_bank', 'https://api.worldbank.org/v2');

        $t1 = microtime(true);
        $responses = \Illuminate\Support\Facades\Http::pool(fn (\Illuminate\Http\Client\Pool $pool) => [
            $pool->as('gdp')->timeout(5)->get("{$baseUrl}/country/{$iso3}/indicator/NY.GDP.MKTP.CD?date={$startYear}:{$endYear}&format=json"),
            $pool->as('gdp_capita')->timeout(5)->get("{$baseUrl}/country/{$iso3}/indicator/NY.GDP.PCAP.CD?date={$startYear}:{$endYear}&format=json"),
            $pool->as('inflation')->timeout(5)->get("{$baseUrl}/country/{$iso3}/indicator/FP.CPI.TOTL.ZG?date={$startYear}:{$endYear}&format=json"),
            $pool->as('population')->timeout(5)->get("{$baseUrl}/country/{$iso3}/indicator/SP.POP.TOTL?date={$startYear}:{$endYear}&format=json"),
            $pool->as('exports')->timeout(5)->get("{$baseUrl}/country/{$iso3}/indicator/NE.EXP.GNFS.CD?date={$startYear}:{$endYear}&format=json"),
            $pool->as('imports')->timeout(5)->get("{$baseUrl}/country/{$iso3}/indicator/NE.IMP.GNFS.CD?date={$startYear}:{$endYear}&format=json"),
        ]);

        $elapsed = round((microtime(true) - $t1) * 1000, 2);

        // Pre-parse the indicators mapping
        $indicatorMapping = self::INDICATORS;

        foreach ($responses as $key => $response) {
            $code = match($key) {
                'gdp' => 'NY.GDP.MKTP.CD',
                'gdp_capita' => 'NY.GDP.PCAP.CD',
                'inflation' => 'FP.CPI.TOTL.ZG',
                'population' => 'SP.POP.TOTL',
                'exports' => 'NE.EXP.GNFS.CD',
                'imports' => 'NE.IMP.GNFS.CD',
            };

            $name = $indicatorMapping[$code];
            $endpoint = "{$baseUrl}/country/{$iso3}/indicator/{$code}?date={$startYear}:{$endYear}&format=json";

            $isSuccess = false;
            $statusCode = 0;
            $responseBody = '';
            $errorMessage = null;

            if ($response instanceof \Illuminate\Http\Client\Response) {
                $statusCode = $response->status();
                $responseBody = $response->body();
                $isSuccess = $response->successful();
                if (!$isSuccess) {
                    $errorMessage = "HTTP Error " . $statusCode;
                }
            } else if ($response instanceof \Throwable) {
                $errorMessage = "Connection failed: " . $response->getMessage();
            }

            // Log each API call manually to api_logs
            $this->logApiCall(
                method: 'GET',
                endpoint: $endpoint,
                statusCode: $statusCode ?: null,
                responseTime: $elapsed,
                responseSize: strlen($responseBody),
                params: ['date' => "{$startYear}:{$endYear}", 'format' => 'json'],
                isSuccess: $isSuccess,
                errorMessage: $errorMessage
            );

            if ($isSuccess && $response instanceof \Illuminate\Http\Client\Response) {
                $data = $response->json();
                if (isset($data[1]) && is_array($data[1])) {
                    $records = $data[1];
                    $latestPopulation = null;
                    $latestPopYear = 0;

                    foreach ($records as $record) {
                        $year = (int) ($record['date'] ?? 0);
                        $value = $record['value'] !== null ? (float) $record['value'] : null;

                        if ($year === 0 || $value === null) {
                            continue;
                        }

                        // Save indicator record
                        EconomicIndicator::updateOrCreate(
                            [
                                'country_id' => $country->id,
                                'indicator_code' => $code,
                                'year' => $year
                            ],
                            [
                                'indicator_name' => $name,
                                'value' => $value,
                                'unit' => $record['unit'] ?: null,
                                'source' => 'World Bank API',
                            ]
                        );

                        // Side Effect: Track latest population value
                        if ($code === 'SP.POP.TOTL') {
                            if ($year > $latestPopYear) {
                                $latestPopYear = $year;
                                $latestPopulation = (int) $value;
                            }
                        }
                    }

                    if ($code === 'SP.POP.TOTL' && $latestPopulation !== null) {
                        Country::where('id', $country->id)->update(['population' => $latestPopulation]);
                    }
                }
            } else {
                throw new \Exception($errorMessage ?? "World Bank API call for indicator {$code} failed.");
            }
        }
    }

    /**
     * Get specific indicator history for a country with cache.
     */
    public function getIndicatorHistory(int $countryId, string $indicatorCode, int $limit = 5, bool $forceRefresh = false): EloquentCollection
    {
        $cacheKey = "country.{$countryId}.indicators.{$indicatorCode}.history.{$limit}";
        $ttl = config('gscrip.cache_ttl.world_bank', 86400); // 24 hours

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $ttl, function () use ($countryId, $indicatorCode, $limit) {
            return $this->indicatorRepository->indicatorHistory($countryId, $indicatorCode, $limit);
        });
    }

    /**
     * Flush all indicators cache tags or keys.
     */
    public function flushCache(): void
    {
        Cache::flush();
    }
}
