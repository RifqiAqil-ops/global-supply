<?php

namespace App\DTOs;

class EconomicIndicatorDTO
{
    public function __construct(
        public readonly string $indicatorCode,
        public readonly string $indicatorName,
        public readonly int $year,
        public readonly ?float $value = null,
        public readonly ?string $unit = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            indicatorCode: $data['indicator_code'] ?? '',
            indicatorName: $data['indicator_name'] ?? '',
            year: (int) ($data['year'] ?? 0),
            value: isset($data['value']) ? (float) $data['value'] : null,
            unit: $data['unit'] ?? null,
        );
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
