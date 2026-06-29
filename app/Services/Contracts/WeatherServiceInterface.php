<?php

namespace App\Services\Contracts;

use App\DTOs\WeatherDTO;

interface WeatherServiceInterface
{
    /**
     * Fetch weather data for a country by its coordinates.
     *
     * @param float $latitude
     * @param float $longitude
     * @return WeatherDTO
     */
    public function fetchByCoordinates(float $latitude, float $longitude): WeatherDTO;
}
