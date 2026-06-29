<?php

namespace App\Http\Clients;

class TestApiClient extends BaseApiClient
{
    public function __construct()
    {
        // Bind to rest countries url using configuration file
        parent::__construct(
            config('gscrip.api.rest_countries', 'https://restcountries.com/v3.1'),
            'REST Countries Test'
        );
    }

    /**
     * Fetch raw data for a specific country by name to verify client mechanics.
     */
    public function testFetch(string $name): array
    {
        return $this->request('GET', "name/{$name}");
    }
}
