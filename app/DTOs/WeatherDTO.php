<?php

namespace App\DTOs;

class WeatherDTO
{
    public function __construct(
        public readonly float $temperature,
        public readonly ?float $feelsLike = null,
        public readonly ?float $humidity = null,
        public readonly ?float $windSpeed = null,
        public readonly ?int $windDirection = null,
        public readonly ?float $precipitation = null,
        public readonly ?float $pressure = null,
        public readonly ?int $weatherCode = null,
        public readonly ?string $weatherDescription = null,
        public readonly bool $isExtreme = false,
        public readonly array $dailyForecast = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            temperature: (float) ($data['temperature'] ?? 0.0),
            feelsLike: isset($data['feels_like']) ? (float) $data['feels_like'] : null,
            humidity: isset($data['humidity']) ? (float) $data['humidity'] : null,
            windSpeed: isset($data['wind_speed']) ? (float) $data['wind_speed'] : null,
            windDirection: isset($data['wind_direction']) ? (int) $data['wind_direction'] : null,
            precipitation: isset($data['precipitation']) ? (float) $data['precipitation'] : null,
            pressure: isset($data['pressure']) ? (float) $data['pressure'] : null,
            weatherCode: isset($data['weather_code']) ? (int) $data['weather_code'] : null,
            weatherDescription: $data['weather_description'] ?? null,
            isExtreme: (bool) ($data['is_extreme'] ?? false),
            dailyForecast: $data['daily_forecast'] ?? [],
        );
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
