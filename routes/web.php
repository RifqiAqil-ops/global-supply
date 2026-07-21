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

Route::get('/system-audit-diagnostic', function() {
    try {
        return response()->json([
            'env' => [
                'APP_ENV' => config('app.env'),
                'APP_DEBUG' => config('app.debug'),
                'APP_URL' => config('app.url'),
                'APP_KEY_SET' => !empty(config('app.key')),
                'CACHE_STORE' => config('cache.default'),
                'SESSION_DRIVER' => config('session.driver'),
                'QUEUE_CONNECTION' => config('queue.default'),
                'FILESYSTEM_DISK' => config('filesystems.default'),
                'LOG_CHANNEL' => config('logging.default'),
            ],
            'db' => [
                'connection' => config('database.default'),
                'countries_count' => \App\Models\Country::count(),
                'ports_count' => \App\Models\Port::count(),
                'exchange_rates_count' => \App\Models\ExchangeRate::count(),
                'weather_data_count' => \App\Models\WeatherData::count(),
                'risk_scores_count' => \App\Models\CountryRiskScore::count(),
                'news_articles_count' => \App\Models\NewsArticle::count(),
                'users_count' => \App\Models\User::count(),
                'system_configs_count' => \App\Models\SystemConfig::count(),
                'activity_logs_count' => \App\Models\ActivityLog::count(),
            ],
            'apis' => [
                'gnews_key' => '7344b28905b738f61c307796531fda31',
            ]
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

Route::get('/', function () {
    if (Auth::check()) {
        return Auth::user()->isAdmin() 
            ? redirect()->route('admin.dashboard')
            : redirect()->route('user.dashboard');
    }
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    if (Auth::check()) {
        return Auth::user()->isAdmin() 
            ? redirect()->route('admin.dashboard')
            : redirect()->route('user.dashboard');
    }
    return redirect()->route('login');
})->name('dashboard');

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
| Authenticated User & Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    // Logout
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');


    // Admin Dashboard
    Route::get('admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // User Dashboard & Modules
    Route::get('user/dashboard', [UserDashboardController::class, 'index'])->name('user.dashboard');
    Route::post('user/refresh-metrics', [UserDashboardController::class, 'refreshMetrics'])->name('user.refresh-metrics');

    Route::get('countries', [\App\Http\Controllers\CountryController::class, 'index'])->name('countries.index');
    Route::get('countries/{country}', [\App\Http\Controllers\CountryController::class, 'show'])->name('countries.show');

    Route::get('currency', [\App\Http\Controllers\User\CurrencyController::class, 'index'])->name('currency.index');

    Route::get('risk', [\App\Http\Controllers\User\RiskHistoryController::class, 'index'])->name('risk.index');
    Route::get('risk-history', [\App\Http\Controllers\User\RiskHistoryController::class, 'index'])->name('risk-history.index');

    Route::get('compare', [\App\Http\Controllers\User\CompareController::class, 'index'])->name('compare.index');

    Route::get('watchlists', [\App\Http\Controllers\User\WatchlistController::class, 'index'])->name('watchlists.index');
    Route::post('watchlists', [\App\Http\Controllers\User\WatchlistController::class, 'store'])->name('watchlists.store');
    Route::put('watchlists/{watchlist}', [\App\Http\Controllers\User\WatchlistController::class, 'update'])->name('watchlists.update');
    Route::delete('watchlists/{watchlist}', [\App\Http\Controllers\User\WatchlistController::class, 'destroy'])->name('watchlists.destroy');

    Route::get('ports', [\App\Http\Controllers\PortController::class, 'index'])->name('ports.index');

    Route::get('weather', [\App\Http\Controllers\User\WeatherController::class, 'index'])->name('weather.index');

    Route::get('news', [\App\Http\Controllers\User\NewsController::class, 'index'])->name('news.index');

    Route::get('articles', [\App\Http\Controllers\User\ArticleController::class, 'index'])->name('articles.index');
    Route::get('articles/{slug}', [\App\Http\Controllers\User\ArticleController::class, 'show'])->name('articles.show');

    // Admin settings
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('diagnose-api', [AdminDashboardController::class, 'diagnoseApi'])->name('diagnose-api');
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->except(['show']);
        Route::resource('ports', \App\Http\Controllers\Admin\PortController::class)->except(['show']);
        Route::resource('articles', \App\Http\Controllers\Admin\ArticleController::class)->except(['show']);

        Route::get('weights', [\App\Http\Controllers\Admin\DashboardController::class, 'weights'])->name('weights.index');
        Route::post('weights', [\App\Http\Controllers\Admin\DashboardController::class, 'updateWeights'])->name('weights.update');

        // External API Data Synchronization Controls
        Route::get('sync', [\App\Http\Controllers\Admin\SyncController::class, 'index'])->name('sync.index');
        Route::post('sync', [\App\Http\Controllers\Admin\SyncController::class, 'runSync'])->name('sync.run');
        Route::post('sync/all', [\App\Http\Controllers\Admin\SyncController::class, 'runAllSync'])->name('sync.run-all');
    });
});
