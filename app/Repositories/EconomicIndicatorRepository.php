<?php

namespace App\Repositories;

use App\Models\EconomicIndicator;
use App\Repositories\Contracts\EconomicIndicatorRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EconomicIndicatorRepository extends BaseRepository implements EconomicIndicatorRepositoryInterface
{
    public function __construct(EconomicIndicator $model)
    {
        parent::__construct($model);
    }

    /**
     * Get the latest values for all indicators of a country.
     */
    public function latestIndicators(int $countryId): Collection
    {
        return $this->model->where('country_id', $countryId)
            ->orderBy('indicator_code')
            ->orderByDesc('year')
            ->get()
            ->unique('indicator_code');
    }

    /**
     * Get the historical values of a specific indicator of a country.
     */
    public function indicatorHistory(int $countryId, string $indicatorCode, int $limit = 5): Collection
    {
        return $this->model->where('country_id', $countryId)
            ->where('indicator_code', $indicatorCode)
            ->orderByDesc('year')
            ->limit($limit)
            ->get();
    }

    /**
     * Get the latest GDP value of a country.
     */
    public function latestGDP(int $countryId): ?EconomicIndicator
    {
        return $this->model->where('country_id', $countryId)
            ->where('indicator_code', 'NY.GDP.MKTP.CD')
            ->orderByDesc('year')
            ->first();
    }

    /**
     * Get the latest Inflation value of a country.
     */
    public function latestInflation(int $countryId): ?EconomicIndicator
    {
        return $this->model->where('country_id', $countryId)
            ->where('indicator_code', 'FP.CPI.TOTL.ZG')
            ->orderByDesc('year')
            ->first();
    }

    /**
     * Get the latest Population value of a country.
     */
    public function latestPopulation(int $countryId): ?EconomicIndicator
    {
        return $this->model->where('country_id', $countryId)
            ->where('indicator_code', 'SP.POP.TOTL')
            ->orderByDesc('year')
            ->first();
    }

    /**
     * Get the latest Exports value of a country.
     */
    public function latestExports(int $countryId): ?EconomicIndicator
    {
        return $this->model->where('country_id', $countryId)
            ->where('indicator_code', 'NE.EXP.GNFS.CD')
            ->orderByDesc('year')
            ->first();
    }

    /**
     * Get the latest Imports value of a country.
     */
    public function latestImports(int $countryId): ?EconomicIndicator
    {
        return $this->model->where('country_id', $countryId)
            ->where('indicator_code', 'NE.IMP.GNFS.CD')
            ->orderByDesc('year')
            ->first();
    }
}
