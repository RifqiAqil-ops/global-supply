<?php

namespace App\Repositories\Contracts;

use App\Interfaces\RepositoryInterface;
use App\Models\ExchangeRate;
use Illuminate\Database\Eloquent\Collection;

interface ExchangeRateRepositoryInterface extends RepositoryInterface
{
    /**
     * Get the latest exchange rate for a currency code.
     *
     * @param string $currencyCode
     * @return ExchangeRate|null
     */
    public function latestRate(string $currencyCode): ?ExchangeRate;

    /**
     * Get the historical exchange rates for a currency code.
     *
     * @param string $currencyCode
     * @param int $limit
     * @return Collection
     */
    public function historicalRates(string $currencyCode, int $limit = 30): Collection;
}
