<?php

namespace App\Repositories;

use App\Models\ExchangeRate;
use App\Repositories\Contracts\ExchangeRateRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ExchangeRateRepository extends BaseRepository implements ExchangeRateRepositoryInterface
{
    public function __construct(ExchangeRate $model)
    {
        parent::__construct($model);
    }

    /**
     * Get the latest exchange rate for a currency code.
     */
    public function latestRate(string $currencyCode): ?ExchangeRate
    {
        return $this->model->where('currency_code', strtoupper($currencyCode))
            ->orderByDesc('rate_date')
            ->first();
    }

    /**
     * Get the historical exchange rates for a currency code.
     */
    public function historicalRates(string $currencyCode, int $limit = 30): Collection
    {
        return $this->model->where('currency_code', strtoupper($currencyCode))
            ->orderByDesc('rate_date')
            ->limit($limit)
            ->get();
    }
}
