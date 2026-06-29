<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Repositories\Contracts\CountryRepositoryInterface::class,
            \App\Repositories\CountryRepository::class
        );
        $this->app->bind(
            \App\Services\Contracts\CountryServiceInterface::class,
            \App\Services\External\RestCountriesService::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\EconomicIndicatorRepositoryInterface::class,
            \App\Repositories\EconomicIndicatorRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\WeatherRepositoryInterface::class,
            \App\Repositories\WeatherRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\ExchangeRateRepositoryInterface::class,
            \App\Repositories\ExchangeRateRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\NewsRepositoryInterface::class,
            \App\Repositories\NewsRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
