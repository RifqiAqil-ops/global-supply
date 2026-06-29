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
     * Get indicators for a country using Laravel cache (TTL = 24 hours).
     */
    public function getLatestIndicators(int $countryId, bool $forceRefresh = false): EloquentCollection
    {
        $cacheKey = "country.{$countryId}.indicators.latest";
        $ttl = config('gscrip.cache_ttl.world_bank', 86400); // 24 hours

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $ttl, function () use ($countryId) {
            return $this->indicatorRepository->latestIndicators($countryId);
        });
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
        // Flush tags or wildcard keys if Redis, else standard flush or keys
        // Since we are using standard database cache by default, let's clear full cache or flush locally
        // Laravel's database store doesn't support tags, so we can run a simple pattern flush if Redis
        // Or clear specifically for processed countries. A simple Cache::flush() is safe or custom keys clearing.
        // Let's clear the entire cache since it's the simplest and safest fallback for database stores.
        Cache::flush();
    }
}
