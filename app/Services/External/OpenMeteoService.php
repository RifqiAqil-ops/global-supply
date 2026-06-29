<?php

namespace App\Services\External;

use App\DTOs\WeatherDTO;
use App\Http\Clients\BaseApiClient;
use App\Models\Country;
use App\Models\WeatherData;
use App\Repositories\Contracts\WeatherRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class OpenMeteoService extends BaseApiClient
{
    protected WeatherRepositoryInterface $weatherRepository;

    public function __construct(WeatherRepositoryInterface $weatherRepository)
    {
        parent::__construct(
            config('gscrip.api.open_meteo', 'https://api.open-meteo.com/v1'),
            'Open-Meteo'
        );
        $this->weatherRepository = $weatherRepository;
    }

    /**
     * Synchronize weather data for all countries.
     *
     * @return array Sync summary counts: ['processed' => X, 'new' => Y, 'updated' => Z, 'failed' => W]
     */
    public function syncAllCountriesWeather(): array
    {
        $summary = [
            'processed' => 0,
            'new' => 0,
            'updated' => 0,
            'failed' => 0,
        ];

        // Retrieve all countries with valid coordinates
        $countries = Country::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        if ($countries->isEmpty()) {
            Log::warning("No countries with valid coordinates found to sync weather.");
            return $summary;
        }

        // Open-Meteo allows up to 50 locations per request in batch
        $chunks = $countries->chunk(50);

        foreach ($chunks as $chunk) {
            try {
                $latitudes = $chunk->pluck('latitude')->map(fn($v) => number_format((float)$v, 4, '.', ''))->implode(',');
                $longitudes = $chunk->pluck('longitude')->map(fn($v) => number_format((float)$v, 4, '.', ''))->implode(',');

                $params = [
                    'query' => [
                        'latitude' => $latitudes,
                        'longitude' => $longitudes,
                        'current' => 'temperature_2m,relative_humidity_2m,apparent_temperature,precipitation,weather_code,pressure_msl,wind_speed_10m,wind_direction_10m,visibility',
                    ]
                ];

                $response = $this->request('GET', 'forecast', $params);

                if (empty($response)) {
                    Log::warning("Open-Meteo API returned empty forecast response during batch sync.");
                    $summary['failed'] += $chunk->count();
                    continue;
                }

                // If only 1 coordinate was queried, response is a single object. Wrap it in array.
                $records = isset($response['current']) ? [$response] : $response;

                // Map chunk records to respective countries (order matches index)
                $chunkArray = $chunk->values(); // reset keys to 0..N

                foreach ($records as $index => $record) {
                    if (!isset($chunkArray[$index])) {
                        continue;
                    }

                    $country = $chunkArray[$index];
                    
                    try {
                        $parsed = $this->parseWeatherData($record, $country->id);
                        if (!$parsed) {
                            $summary['failed']++;
                            continue;
                        }

                        // Use our 30-minute threshold to prevent database record bloating
                        $halfHourAgo = now()->subMinutes(30);
                        $existing = WeatherData::where('country_id', $country->id)
                            ->where('fetched_at', '>=', $halfHourAgo)
                            ->orderByDesc('fetched_at')
                            ->first();

                        if ($existing) {
                            $existing->update($parsed);
                            $summary['updated']++;
                        } else {
                            WeatherData::create($parsed);
                            $summary['new']++;
                        }

                        $summary['processed']++;

                        // Clear cache for this country's latest weather
                        Cache::forget("country.{$country->id}.weather.latest");

                    } catch (Throwable $e) {
                        Log::error("Failed to sync weather for country '{$country->name}': " . $e->getMessage());
                        $summary['failed']++;
                    }
                }

            } catch (Throwable $e) {
                Log::error("Failed to process batch of weather data: " . $e->getMessage());
                $summary['failed'] += $chunk->count();
            }
        }

        return $summary;
    }

    /**
     * Get the latest weather for a country, utilising cache (TTL = 30 mins).
     */
    public function getLatestWeather(int $countryId, bool $forceRefresh = false): ?WeatherData
    {
        $cacheKey = "country.{$countryId}.weather.latest";
        $ttl = config('gscrip.cache_ttl.weather', 1800); // 30 mins

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $ttl, function () use ($countryId) {
            return $this->weatherRepository->latestWeather($countryId);
        });
    }

    /**
     * Get the historical weather records for a country.
     */
    public function getWeatherHistory(int $countryId, int $limit = 10): EloquentCollection
    {
        return $this->weatherRepository->weatherHistory($countryId, $limit);
    }

    /**
     * Force API refresh for a single country's weather.
     */
    public function refreshCountryWeather(int $countryId): WeatherData
    {
        $country = Country::findOrFail($countryId);
        
        if ($country->latitude === null || $country->longitude === null) {
            throw new \Exception("Country '{$country->name}' does not have coordinates defined.");
        }

        $params = [
            'query' => [
                'latitude' => number_format((float)$country->latitude, 4, '.', ''),
                'longitude' => number_format((float)$country->longitude, 4, '.', ''),
                'current' => 'temperature_2m,relative_humidity_2m,apparent_temperature,precipitation,weather_code,pressure_msl,wind_speed_10m,wind_direction_10m,visibility',
            ]
        ];

        $response = $this->request('GET', 'forecast', $params);

        if (empty($response) || !isset($response['current'])) {
            throw new \Exception("Invalid or empty response from Open-Meteo API.");
        }

        $parsed = $this->parseWeatherData($response, $country->id);

        if (!$parsed) {
            throw new \Exception("Failed to parse weather data response.");
        }

        // Use 30 minute threshold
        $halfHourAgo = now()->subMinutes(30);
        $existing = WeatherData::where('country_id', $countryId)
            ->where('fetched_at', '>=', $halfHourAgo)
            ->orderByDesc('fetched_at')
            ->first();

        if ($existing) {
            $existing->update($parsed);
            $weather = $existing;
        } else {
            $weather = WeatherData::create($parsed);
        }

        // Clear cache
        Cache::forget("country.{$countryId}.weather.latest");

        return $weather;
    }

    /**
     * Parse weather record attributes safely.
     */
    private function parseWeatherData(array $record, int $countryId): ?array
    {
        $current = $record['current'] ?? null;
        if (!$current) {
            return null;
        }

        $temp = isset($current['temperature_2m']) ? (float) $current['temperature_2m'] : 0.0;
        $apparentTemp = isset($current['apparent_temperature']) ? (float) $current['apparent_temperature'] : null;
        $humidity = isset($current['relative_humidity_2m']) ? (float) $current['relative_humidity_2m'] : null;
        $windSpeed = isset($current['wind_speed_10m']) ? (float) $current['wind_speed_10m'] : null;
        $windDirection = isset($current['wind_direction_10m']) ? (int) $current['wind_direction_10m'] : null;
        $precipitation = isset($current['precipitation']) ? (float) $current['precipitation'] : null;
        $pressure = isset($current['pressure_msl']) ? (float) $current['pressure_msl'] : null;
        $visibility = isset($current['visibility']) ? (float) $current['visibility'] : null;
        $weatherCode = isset($current['weather_code']) ? (int) $current['weather_code'] : null;

        // Description from code
        $description = $weatherCode !== null ? $this->translateWeatherCode($weatherCode) : 'Unknown';

        // Extreme weather calculation
        $isExtreme = false;
        if ($temp > 40.0 || $temp < -15.0) {
            $isExtreme = true;
        }
        if ($windSpeed !== null && $windSpeed > 60.0) {
            $isExtreme = true;
        }
        if ($visibility !== null && $visibility < 1000.0) { // Visibility < 1km
            $isExtreme = true;
        }
        if ($precipitation !== null && $precipitation > 50.0) { // Extreme rain
            $isExtreme = true;
        }
        if ($weatherCode !== null && in_array($weatherCode, [95, 96, 99])) { // Thunderstorms with hail
            $isExtreme = true;
        }

        $observationTime = isset($current['time']) ? Carbon::parse($current['time']) : now();

        return [
            'country_id' => $countryId,
            'city_name' => null, // Ibu kota can be resolved if needed, else empty
            'latitude' => isset($record['latitude']) ? (float) $record['latitude'] : null,
            'longitude' => isset($record['longitude']) ? (float) $record['longitude'] : null,
            'temperature' => $temp,
            'feels_like' => $apparentTemp,
            'humidity' => $humidity,
            'wind_speed' => $windSpeed,
            'wind_direction' => $windDirection,
            'precipitation' => $precipitation,
            'pressure' => $pressure,
            'visibility' => $visibility,
            'uv_index' => null, // Open-meteo current doesn't output uv index unless daily/hourly parameter is asked
            'weather_code' => $weatherCode,
            'weather_description' => $description,
            'is_extreme' => $isExtreme,
            'daily_forecast' => null,
            'fetched_at' => $observationTime,
        ];
    }

    /**
     * WMO weather code translator.
     */
    private function translateWeatherCode(int $code): string
    {
        return match ($code) {
            0 => 'Clear sky',
            1 => 'Mainly clear',
            2 => 'Partly cloudy',
            3 => 'Overcast',
            45 => 'Fog',
            48 => 'Depositing rime fog',
            51 => 'Light drizzle',
            53 => 'Moderate drizzle',
            55 => 'Dense drizzle',
            56 => 'Light freezing drizzle',
            57 => 'Dense freezing drizzle',
            61 => 'Slight rain',
            63 => 'Moderate rain',
            65 => 'Heavy rain',
            66 => 'Light freezing rain',
            67 => 'Heavy freezing rain',
            71 => 'Slight snow fall',
            73 => 'Moderate snow fall',
            75 => 'Heavy snow fall',
            77 => 'Snow grains',
            80 => 'Slight rain showers',
            81 => 'Moderate rain showers',
            82 => 'Violent rain showers',
            85 => 'Slight snow showers',
            86 => 'Heavy snow showers',
            95 => 'Thunderstorm',
            96 => 'Thunderstorm with slight hail',
            99 => 'Thunderstorm with heavy hail',
            default => 'Unknown weather condition',
        };
    }

    /**
     * Flush weather cache key patterns.
     */
    public function flushCache(): void
    {
        Cache::flush();
    }
}
