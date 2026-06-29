<?php

namespace App\Repositories\Contracts;

use App\Interfaces\RepositoryInterface;
use App\Models\WeatherData;
use Illuminate\Database\Eloquent\Collection;

interface WeatherRepositoryInterface extends RepositoryInterface
{
    /**
     * Get the latest weather data for a country.
     *
     * @param int $countryId
     * @return WeatherData|null
     */
    public function latestWeather(int $countryId): ?WeatherData;

    /**
     * Get the historical weather records for a country.
     *
     * @param int $countryId
     * @param int $limit
     * @return Collection
     */
    public function weatherHistory(int $countryId, int $limit = 10): Collection;
}
