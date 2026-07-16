<?php

namespace App\Services\External;

use App\DTOs\CountryDTO;
use App\Http\Clients\BaseApiClient;
use App\Models\Country;
use App\Repositories\Contracts\CountryRepositoryInterface;
use App\Services\Contracts\CountryServiceInterface;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class RestCountriesService extends BaseApiClient implements CountryServiceInterface
{
    private const SOVEREIGN_ISO3 = [
        "AFG", "ALB", "DZA", "AND", "AGO", "ATG", "ARG", "ARM", "AUS", "AUT",
        "AZE", "BHS", "BHR", "BGD", "BRB", "BLR", "BEL", "BLZ", "BEN", "BTN",
        "BOL", "BIH", "BWA", "BRA", "BRN", "BGR", "BFA", "BDI", "CPV", "KHM",
        "CMR", "CAN", "CAF", "TCD", "CHL", "CHN", "COL", "COM", "COD", "COG",
        "CRI", "CIV", "HRV", "CUB", "CYP", "CZE", "DNK", "DJI", "DMA", "DOM",
        "ECU", "EGY", "SLV", "GNQ", "ERI", "EST", "SWZ", "ETH", "FJI", "FIN",
        "FRA", "GAB", "GMB", "GEO", "DEU", "GHA", "GRC", "GRD", "GTM", "GIN",
        "GNB", "GUY", "HTI", "HND", "HUN", "ISL", "IND", "IDN", "IRN", "IRQ",
        "IRL", "ISR", "ITA", "JAM", "JPN", "JOR", "KAZ", "KEN", "KIR", "KWT",
        "KGZ", "LAO", "LVA", "LBN", "LSO", "LBR", "LBY", "LIE", "LTU", "LUX",
        "MDG", "MWI", "MYS", "MDV", "MLI", "MLT", "MHL", "MRT", "MUS", "MEX",
        "FSM", "MDA", "MCO", "MNG", "MNE", "MAR", "MOZ", "MMR", "NAM", "NRU",
        "NPL", "NLD", "NZL", "NIC", "NER", "NGA", "PRK", "MKD", "NOR", "OMN",
        "PAK", "PLW", "PSE", "PAN", "PNG", "PRY", "PER", "PHL", "POL", "PRT",
        "QAT", "ROU", "RUS", "RWA", "KNA", "LCA", "VCT", "WSM", "SMR", "STP",
        "SAU", "SEN", "SRB", "SYC", "SLE", "SGP", "SVK", "SVN", "SLB", "SOM",
        "ZAF", "KOR", "SSD", "ESP", "LKA", "SDN", "SUR", "SWE", "CHE", "SYR",
        "TJK", "TZA", "THA", "TLS", "TGO", "TON", "TTO", "TUN", "TUR", "TKM",
        "TUV", "UGA", "UKR", "ARE", "GBR", "USA", "URY", "UZB", "VUT", "VAT",
        "VEN", "VNM", "YEM", "ZMB", "ZWE"
    ];

    protected CountryRepositoryInterface $countryRepository;

    public function __construct(CountryRepositoryInterface $countryRepository)
    {
        parent::__construct(
            config('gscrip.api.rest_countries', 'https://restcountries.com/v3.1'),
            'REST Countries'
        );
        $this->countryRepository = $countryRepository;
    }

    /**
     * Fetch country info by ISO code. Always attempts real-time fetch first, falls back to DB cache if API fails.
     */
    public function fetchByIso(string $isoCode): CountryDTO
    {
        try {
            // Attempt to refresh data from API
            $country = $this->refreshCountry($isoCode);
            $dto = $this->mapToDTO($country);
            $dto->isCached = false;
            return $dto;
        } catch (Throwable $e) {
            Log::warning("REST Countries API call failed for ISO '{$isoCode}', falling back to database: " . $e->getMessage());
            
            // Fallback to database
            $country = $this->countryRepository->findByCode($isoCode);
            if ($country) {
                $dto = $this->mapToDTO($country);
                $dto->isCached = true;
                return $dto;
            }
            
            // If neither works, throw the exception
            throw $e;
        }
    }

    /**
     * Fetch and synchronize all countries from API to database.
     *
     * @return array Array summarizing counts: ['new' => X, 'updated' => Y, 'failed' => Z]
     */
    public function syncAllCountries(): array
    {
        $summary = ['new' => 0, 'updated' => 0, 'failed' => 0];

        try {
            // Retrieve all countries fields from rest countries API mirror
            $data = $this->request('GET', 'countries.json');

            if (empty($data)) {
                Log::warning("REST Countries API returned empty dataset during sync.");
                return $summary;
            }

            foreach ($data as $item) {
                try {
                    $iso3 = strtoupper($item['cca3'] ?? '');
                    if (!in_array($iso3, self::SOVEREIGN_ISO3)) {
                        continue; // Skip non-sovereign territories
                    }

                    $parsed = $this->parseCountryData($item);
                    if (!$parsed) {
                        $summary['failed']++;
                        continue;
                    }

                    $existing = Country::where('iso2', $parsed['iso2'])
                        ->orWhere('iso3', $parsed['iso3'])
                        ->first();

                    if ($existing) {
                        $existing->update($parsed);
                        $summary['updated']++;
                    } else {
                        Country::create($parsed);
                        $summary['new']++;
                    }
                } catch (Throwable $e) {
                    $summary['failed']++;
                    Log::error("Failed to parse and store country: " . ($item['name']['common'] ?? 'Unknown') . " - " . $e->getMessage());
                }
            }

            // Remove any non-sovereign countries from database
            Country::whereNotIn('iso3', self::SOVEREIGN_ISO3)->delete();

            // Flush the full country list cache
            Cache::forget('countries.all');

        } catch (Throwable $e) {
            Log::error("REST Countries bulk sync failed: " . $e->getMessage());
            throw $e;
        }

        return $summary;
    }

    /**
     * Retrieve all countries, utilizing cache for 7 days.
     */
    public function getAllCountries(bool $forceRefresh = false): EloquentCollection
    {
        $cacheKey = 'countries.all';
        $ttl = config('gscrip.cache_ttl.countries', 604800); // 7 days

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $ttl, function () {
            return $this->countryRepository->all();
        });
    }

    /**
     * Get a country by ISO code.
     */
    public function getCountryByCode(string $code): ?Country
    {
        return $this->countryRepository->findByCode($code);
    }

    /**
     * Get a country by name.
     */
    public function getCountryByName(string $name): ?Country
    {
        return $this->countryRepository->findByName($name);
    }

    /**
     * Search countries database.
     */
    public function searchCountries(string $query): EloquentCollection
    {
        return $this->countryRepository->search($query);
    }

    /**
     * Pull specific country from API, save to DB, and clear specific cache.
     */
    public function refreshCountry(string $code): Country
    {
        $code = trim(strtoupper($code));
        $data = Cache::remember('countries.api_raw', 300, function () {
            return $this->request('GET', 'countries.json');
        });

        if (empty($data)) {
            throw new \Exception("Country dataset is empty.");
        }

        $match = collect($data)->first(function ($item) use ($code) {
            return strtoupper($item['cca2'] ?? '') === $code 
                || strtoupper($item['cca3'] ?? '') === $code;
        });

        if (!$match) {
            throw new \Exception("Country code '{$code}' not found on REST Countries API.");
        }

        $iso3 = strtoupper($match['cca3'] ?? '');
        if (!in_array($iso3, self::SOVEREIGN_ISO3)) {
            throw new \Exception("Non-sovereign territory '{$code}' is not supported.");
        }

        $parsed = $this->parseCountryData($match);

        if (!$parsed) {
            throw new \Exception("Failed to parse REST Countries response for '{$code}'.");
        }

        $country = Country::updateOrCreate(
            ['iso2' => $parsed['iso2']],
            $parsed
        );

        // Clear general cache
        Cache::forget('countries.all');

        return $country;
    }

    /**
     * Safely parse raw JSON object from REST Countries API to database array.
     */
    private function parseCountryData(array $item): ?array
    {
        $iso2 = $item['cca2'] ?? null;
        $iso3 = $item['cca3'] ?? null;
        $name = $item['name']['common'] ?? null;

        if (!$iso2 || !$iso3 || !$name) {
            return null;
        }

        // Currency parser
        $currencyCode = null;
        $currencyName = null;
        $currencySymbol = null;
        if (isset($item['currencies']) && is_array($item['currencies'])) {
            $currencyCodes = array_keys($item['currencies']);
            if (count($currencyCodes) > 0) {
                $currencyCode = $currencyCodes[0];
                $currencyName = $item['currencies'][$currencyCode]['name'] ?? null;
                $currencySymbol = $item['currencies'][$currencyCode]['symbol'] ?? null;
            }
        }

        // Calling code parser
        $callingCode = null;
        if (isset($item['idd']['root'])) {
            $suffix = $item['idd']['suffixes'][0] ?? '';
            $callingCode = $item['idd']['root'] . $suffix;
        }

        $data = [
            'iso2' => strtoupper($iso2),
            'iso3' => strtoupper($iso3),
            'name' => $name,
            'official_name' => $item['name']['official'] ?? null,
            'capital' => isset($item['capital']) && is_array($item['capital']) ? implode(', ', $item['capital']) : null,
            'region' => $item['region'] ?? null,
            'sub_region' => $item['subregion'] ?? null,
            'latitude' => isset($item['latlng'][0]) ? (float) $item['latlng'][0] : null,
            'longitude' => isset($item['latlng'][1]) ? (float) $item['latlng'][1] : null,
            'area' => isset($item['area']) ? (float) $item['area'] : null,
            'flag_url' => 'https://flagcdn.com/w320/' . strtolower($iso2) . '.png',
            'flag_emoji' => $item['flag'] ?? null,
            'currency_code' => $currencyCode,
            'currency_name' => $currencyName,
            'currency_symbol' => $currencySymbol,
            'calling_code' => $callingCode,
            'tld' => isset($item['tld']) && is_array($item['tld']) && count($item['tld']) > 0 ? $item['tld'][0] : null,
            'timezones' => $item['timezones'] ?? [],
            'languages' => $item['languages'] ?? [],
            'borders' => $item['borders'] ?? [],
            'is_independent' => (bool) ($item['independent'] ?? true),
            'is_un_member' => (bool) ($item['unMember'] ?? false),
        ];

        if (isset($item['population'])) {
            $data['population'] = (int) $item['population'];
        }

        return $data;
    }

    /**
     * Map Country Eloquent model instance to CountryDTO.
     */
    private function mapToDTO(Country $country): CountryDTO
    {
        return new CountryDTO(
            iso2: $country->iso2,
            iso3: $country->iso3,
            name: $country->name,
            officialName: $country->official_name,
            capital: $country->capital,
            region: $country->region,
            subRegion: $country->sub_region,
            latitude: $country->latitude ? (float) $country->latitude : null,
            longitude: $country->longitude ? (float) $country->longitude : null,
            population: (int) $country->population,
            area: $country->area ? (float) $country->area : null,
            flagUrl: $country->flag_url,
            flagEmoji: $country->flag_emoji,
            currencyCode: $country->currency_code,
            currencyName: $country->currency_name,
            currencySymbol: $country->currency_symbol,
            timezones: $country->timezones ?? [],
            languages: $country->languages ?? [],
            borders: $country->borders ?? [],
            tld: $country->tld,
        );
    }
}
