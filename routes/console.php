<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Console Task Schedules
|--------------------------------------------------------------------------
|
| Automated background data synchronization schedule for Waypoint platform.
| Heavy API fetch jobs run without overlapping to guarantee zero UI latency.
|
*/

// 1. Countries Master Data - Weekly
Schedule::command('gscrip:sync-countries')->weekly()->withoutOverlapping()->onOneServer();

// 2. Exchange Rates - Every 1 Hour
Schedule::command('gscrip:sync-exchange')->hourly()->withoutOverlapping()->onOneServer();

// 3. Open-Meteo Weather Forecasts - Every 30 Minutes
Schedule::command('gscrip:sync-weather')->everyThirtyMinutes()->withoutOverlapping()->onOneServer();

// 4. World Bank Macroeconomic Indicators - Daily
Schedule::command('gscrip:sync-worldbank')->daily()->withoutOverlapping()->onOneServer();

// 5. GNews Geopolitical & Supply Chain Feed - Every 1 Hour
Schedule::command('gscrip:sync-news')->hourly()->withoutOverlapping()->onOneServer();

// 6. Recalculate Country Composite Risk Index - Every 1 Hour
Schedule::command('gscrip:recalculate-risk')->hourly()->withoutOverlapping()->onOneServer();
