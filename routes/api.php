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
