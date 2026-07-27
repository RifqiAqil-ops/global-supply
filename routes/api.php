<?php

use App\Http\Controllers\API\ApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/countries', [ApiController::class, 'countries']);
Route::get('/risk', [ApiController::class, 'risk']);
Route::get('/ports', [ApiController::class, 'ports']);
Route::get('/news', [ApiController::class, 'news']);
Route::get('/currency', [ApiController::class, 'currency']);

Route::get('/live-dashboard-metrics', [\App\Http\Controllers\LiveApiController::class, 'dashboardMetrics']);
Route::get('/live-weather', [\App\Http\Controllers\LiveApiController::class, 'weather']);
Route::get('/live-exchange-rates', [\App\Http\Controllers\LiveApiController::class, 'exchangeRates']);
Route::get('/live-news', [\App\Http\Controllers\LiveApiController::class, 'news']);
Route::get('/live-country-risk/{code}', [\App\Http\Controllers\LiveApiController::class, 'countryRisk']);
