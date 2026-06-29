<?php

namespace App\DTOs;

class CountryDTO
{
    public function __construct(
        public readonly string $iso2,
        public readonly string $iso3,
        public readonly string $name,
        public readonly ?string $officialName = null,
        public readonly ?string $capital = null,
        public readonly ?string $region = null,
        public readonly ?string $subRegion = null,
        public readonly ?float $latitude = null,
        public readonly ?float $longitude = null,
        public readonly int $population = 0,
        public readonly ?float $area = null,
        public readonly ?string $flagUrl = null,
        public readonly ?string $flagEmoji = null,
        public readonly ?string $currencyCode = null,
        public readonly ?string $currencyName = null,
        public readonly ?string $currencySymbol = null,
        public readonly array $timezones = [],
        public readonly array $languages = [],
        public readonly array $borders = [],
    ) {}

    /**
     * Create DTO from array data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            iso2: $data['iso2'] ?? '',
            iso3: $data['iso3'] ?? '',
            name: $data['name'] ?? '',
            officialName: $data['official_name'] ?? null,
            capital: $data['capital'] ?? null,
            region: $data['region'] ?? null,
            subRegion: $data['sub_region'] ?? null,
            latitude: isset($data['latitude']) ? (float) $data['latitude'] : null,
            longitude: isset($data['longitude']) ? (float) $data['longitude'] : null,
            population: (int) ($data['population'] ?? 0),
            area: isset($data['area']) ? (float) $data['area'] : null,
            flagUrl: $data['flag_url'] ?? null,
            flagEmoji: $data['flag_emoji'] ?? null,
            currencyCode: $data['currency_code'] ?? null,
            currencyName: $data['currency_name'] ?? null,
            currencySymbol: $data['currency_symbol'] ?? null,
            timezones: $data['timezones'] ?? [],
            languages: $data['languages'] ?? [],
            borders: $data['borders'] ?? [],
        );
    }

    /**
     * Convert DTO to array.
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
