<?php

namespace App\Repositories;

use App\Models\WeatherData;
use App\Repositories\Contracts\WeatherRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class WeatherRepository extends BaseRepository implements WeatherRepositoryInterface
{
    public function __construct(WeatherData $model)
    {
        parent::__construct($model);
    }

    /**
     * Get the latest weather data for a country.
     */
    public function latestWeather(int $countryId): ?WeatherData
    {
        return $this->model->where('country_id', $countryId)
            ->orderByDesc('fetched_at')
            ->first();
    }

    /**
     * Get the historical weather records for a country.
     */
    public function weatherHistory(int $countryId, int $limit = 10): Collection
    {
        return $this->model->where('country_id', $countryId)
            ->orderByDesc('fetched_at')
            ->limit($limit)
            ->get();
    }
}
