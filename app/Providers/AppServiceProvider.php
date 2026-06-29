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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
