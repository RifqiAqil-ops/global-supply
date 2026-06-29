<?php

namespace App\DTOs;

class ExchangeRateDTO
{
    public bool $isCached = false;

    public function __construct(
        public readonly string $currencyCode,
        public readonly float $rateToUsd,
        public readonly ?float $rateToIdr = null,
        public readonly ?float $changePercent = null,
        public readonly string $rateDate = '',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            currencyCode: $data['currency_code'] ?? '',
            rateToUsd: (float) ($data['rate_to_usd'] ?? 0.0),
            rateToIdr: isset($data['rate_to_idr']) ? (float) $data['rate_to_idr'] : null,
            changePercent: isset($data['change_percent']) ? (float) $data['change_percent'] : null,
            rateDate: $data['rate_date'] ?? date('Y-m-d'),
        );
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
