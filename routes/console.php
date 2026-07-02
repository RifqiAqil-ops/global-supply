<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

Schedule::command('gscrip:sync-weather')->hourly()->withoutOverlapping();
Schedule::command('gscrip:sync-exchange')->daily()->withoutOverlapping();
Schedule::command('gscrip:sync-news')->hourly()->withoutOverlapping();
Schedule::command('gscrip:recalculate-risk')->hourly()->withoutOverlapping();
