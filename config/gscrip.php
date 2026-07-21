<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Base URLs
    |--------------------------------------------------------------------------
    */
    'api' => [
        'open_meteo' => env('OPEN_METEO_BASE_URL', 'https://api.open-meteo.com/v1'),
        'world_bank' => env('WORLD_BANK_BASE_URL', 'https://api.worldbank.org/v2'),
        'rest_countries' => env('REST_COUNTRIES_BASE_URL', 'https://raw.githubusercontent.com/mledoze/countries/master'),
        'exchange_rate' => env('EXCHANGERATE_BASE_URL', 'https://api.exchangerate-api.com/v4'),
        'gnews' => [
            'base_url' => env('GNEWS_BASE_URL', 'https://gnews.io/api/v4'),
            'key' => env('GNEWS_API_KEY', '7344b58e727ec29a67a0701ae00021c3'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Global Client Options
    |--------------------------------------------------------------------------
    */
    'options' => [
        'timeout' => (int) env('API_TIMEOUT_SECONDS', 10),
        'retry' => (int) env('API_RETRY_ATTEMPTS', 3),
        'retry_delay' => (int) env('API_RETRY_DELAY_MS', 100),
        'user_agent' => env('API_USER_AGENT', 'Waypoint-Client/1.0.0 (Global Supply Chain Intelligence)'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache TTL Settings (in seconds)
    |--------------------------------------------------------------------------
    */
    'cache_ttl' => [
        'weather' => (int) env('CACHE_TTL_WEATHER', 1800),             // 30 minutes
        'exchange_rate' => (int) env('CACHE_TTL_EXCHANGE_RATE', 3600), // 1 hour
        'news' => (int) env('CACHE_TTL_NEWS', 3600),                   // 1 hour
        'countries' => (int) env('CACHE_TTL_COUNTRIES', 604800),       // 7 days
        'world_bank' => (int) env('CACHE_TTL_WORLD_BANK', 86400),      // 24 hours
        'port_dataset' => (int) env('CACHE_TTL_PORT_DATASET', 2592000),// 30 days
    ],

    /*
    |--------------------------------------------------------------------------
    | System Defaults
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'country' => env('DEFAULT_COUNTRY_ISO2', 'ID'),
        'currency' => env('DEFAULT_CURRENCY', 'IDR'),
    ],
];
