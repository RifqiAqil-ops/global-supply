<?php

namespace App\Repositories\Contracts;

use App\Interfaces\RepositoryInterface;
use App\Models\EconomicIndicator;
use Illuminate\Database\Eloquent\Collection;

interface EconomicIndicatorRepositoryInterface extends RepositoryInterface
{
    /**
     * Get the latest values for all indicators of a country.
     *
     * @param int $countryId
     * @return Collection
     */
    public function latestIndicators(int $countryId): Collection;

    /**
     * Get the historical values of a specific indicator of a country.
     *
     * @param int $countryId
     * @param string $indicatorCode
     * @param int $limit
     * @return Collection
     */
    public function indicatorHistory(int $countryId, string $indicatorCode, int $limit = 5): Collection;

    /**
     * Get the latest GDP value of a country.
     *
     * @param int $countryId
     * @return EconomicIndicator|null
     */
    public function latestGDP(int $countryId): ?EconomicIndicator;

    /**
     * Get the latest Inflation value of a country.
     *
     * @param int $countryId
     * @return EconomicIndicator|null
     */
    public function latestInflation(int $countryId): ?EconomicIndicator;

    /**
     * Get the latest Population value of a country.
     *
     * @param int $countryId
     * @return EconomicIndicator|null
     */
    public function latestPopulation(int $countryId): ?EconomicIndicator;

    /**
     * Get the latest Exports value of a country.
     *
     * @param int $countryId
     * @return EconomicIndicator|null
     */
    public function latestExports(int $countryId): ?EconomicIndicator;

    /**
     * Get the latest Imports value of a country.
     *
     * @param int $countryId
     * @return EconomicIndicator|null
     */
    public function latestImports(int $countryId): ?EconomicIndicator;
}
