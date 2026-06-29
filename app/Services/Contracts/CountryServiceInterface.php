<?php

namespace App\Services\Contracts;

use App\DTOs\CountryDTO;

interface CountryServiceInterface
{
    /**
     * Fetch country info by ISO code.
     *
     * @param string $isoCode
     * @return CountryDTO
     */
    public function fetchByIso(string $isoCode): CountryDTO;
}
