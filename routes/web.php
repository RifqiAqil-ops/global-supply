<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (Auth::check()) {
        return Auth::user()->isAdmin() 
            ? redirect()->route('admin.dashboard')
            : redirect()->route('user.dashboard');
    }
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Authentication Routes (Guest)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    // Login
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);

    // Register
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);

    // Forgot Password
    Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

    // Reset Password
    Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    /*
    |--------------------------------------------------------------------------
    | User Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('user')->name('user.')->group(function () {
        Route::get('dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
        Route::post('refresh-metrics', [UserDashboardController::class, 'refreshMetrics'])->name('refresh-metrics');
    });

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('diagnose-api', [AdminDashboardController::class, 'diagnoseApi'])->name('diagnose-api');
        Route::post('weights/update', [AdminDashboardController::class, 'updateWeights'])->name('weights.update');

        // Automated Data Sync Manager
        Route::get('sync', [\App\Http\Controllers\Admin\SyncController::class, 'index'])->name('sync.index');
        Route::post('sync/run', [\App\Http\Controllers\Admin\SyncController::class, 'runSync'])->name('sync.run');
        Route::post('sync/run-all', [\App\Http\Controllers\Admin\SyncController::class, 'runAllSync'])->name('sync.run-all');

        // Operations Center & System Health
        Route::get('operations', [\App\Http\Controllers\Admin\OperationsController::class, 'index'])->name('operations.index');
        Route::get('health', [\App\Http\Controllers\Admin\OperationsController::class, 'health'])->name('health.index');
        Route::get('api-monitoring', [\App\Http\Controllers\Admin\OperationsController::class, 'apiMonitoring'])->name('api-monitoring.index');
        Route::get('observability', [\App\Http\Controllers\Admin\ObservabilityController::class, 'index'])->name('observability.index');

        // Failed Jobs Management
        Route::get('failed-jobs', [\App\Http\Controllers\Admin\FailedJobsController::class, 'index'])->name('failed-jobs.index');
        Route::post('failed-jobs/retry/{id}', [\App\Http\Controllers\Admin\FailedJobsController::class, 'retry'])->name('failed-jobs.retry');
        Route::post('failed-jobs/retry-all', [\App\Http\Controllers\Admin\FailedJobsController::class, 'retryAll'])->name('failed-jobs.retry-all');
        Route::delete('failed-jobs/{id}', [\App\Http\Controllers\Admin\FailedJobsController::class, 'destroy'])->name('failed-jobs.destroy');
        Route::post('failed-jobs/flush', [\App\Http\Controllers\Admin\FailedJobsController::class, 'flush'])->name('failed-jobs.flush');
    });

    /*
    |--------------------------------------------------------------------------
    | Shared Placeholder Routes
    |--------------------------------------------------------------------------
    */
    Route::get('countries', [\App\Http\Controllers\CountryController::class, 'index'])->name('countries.index');

    Route::get('countries/{code}', [\App\Http\Controllers\CountryController::class, 'show'])->name('countries.show');

    Route::get('ports', [\App\Http\Controllers\PortController::class, 'index'])->name('ports.index');

    Route::get('risk-history', [\App\Http\Controllers\User\RiskHistoryController::class, 'index'])->name('risk-history.index');

    Route::prefix('live-api')->name('live-api.')->group(function () {
        Route::get('dashboard-metrics', [\App\Http\Controllers\User\LiveUpdateController::class, 'dashboardMetrics'])->name('dashboard-metrics');
        Route::get('weather', [\App\Http\Controllers\User\LiveUpdateController::class, 'weather'])->name('weather');
        Route::get('exchange-rates', [\App\Http\Controllers\User\LiveUpdateController::class, 'exchangeRates'])->name('exchange-rates');
        Route::get('news', [\App\Http\Controllers\User\LiveUpdateController::class, 'news'])->name('news');
        Route::get('country-risk/{code}', [\App\Http\Controllers\User\LiveUpdateController::class, 'countryRisk'])->name('country-risk');
    });

    Route::resource('watchlists', \App\Http\Controllers\User\WatchlistController::class)->only([
        'index', 'store', 'update', 'destroy'
    ]);

    Route::get('compare', [\App\Http\Controllers\User\CompareController::class, 'index'])->name('compare.index');

    Route::get('currency', [\App\Http\Controllers\User\CurrencyController::class, 'index'])->name('currency.index');

    Route::get('weather', [\App\Http\Controllers\User\WeatherController::class, 'index'])->name('weather.index');

    Route::get('news', [\App\Http\Controllers\User\NewsController::class, 'index'])->name('news.index');

    Route::get('articles', [\App\Http\Controllers\User\ArticleController::class, 'index'])->name('articles.index');
    Route::get('articles/{slug}', [\App\Http\Controllers\User\ArticleController::class, 'show'])->name('articles.show');

    // Admin settings
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->except(['show']);
        Route::resource('ports', \App\Http\Controllers\Admin\PortController::class)->except(['show']);
        Route::resource('articles', \App\Http\Controllers\Admin\ArticleController::class)->except(['show']);

        Route::get('weights', function () {
            return view('placeholders.module', ['title' => 'Risk Weights', 'icon' => 'bi-sliders']);
        })->name('weights.index');
    });
});
