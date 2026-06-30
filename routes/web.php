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
    Route::get('reports', [\App\Http\Controllers\User\ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export/pdf', [\App\Http\Controllers\User\ReportController::class, 'exportPdf'])->name('reports.export.pdf');
    Route::get('reports/export/csv', [\App\Http\Controllers\User\ReportController::class, 'exportCsv'])->name('reports.export.csv');

    Route::get('watchlists', function () {
        return view('placeholders.module', ['title' => 'Watchlists', 'icon' => 'bi-eye']);
    })->name('watchlists.index');

    Route::get('compare', function () {
        return view('placeholders.module', ['title' => 'Country Compare', 'icon' => 'bi-shuffle']);
    })->name('compare.index');

    Route::get('currency', function () {
        return view('placeholders.module', ['title' => 'Currency Monitor', 'icon' => 'bi-currency-exchange']);
    })->name('currency.index');

    Route::get('weather', function () {
        return view('placeholders.module', ['title' => 'Weather Alerts', 'icon' => 'bi-cloud-lightning-rain']);
    })->name('weather.index');

    Route::get('news', function () {
        return view('placeholders.module', ['title' => 'Geopolitical News', 'icon' => 'bi-newspaper']);
    })->name('news.index');

    // Admin Placeholder settings
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('users', function () {
            return view('placeholders.module', ['title' => 'User Manager', 'icon' => 'bi-people']);
        })->name('users.index');

        Route::get('weights', function () {
            return view('placeholders.module', ['title' => 'Risk Weights', 'icon' => 'bi-sliders']);
        })->name('weights.index');

        Route::get('api-health', function () {
            return view('placeholders.module', ['title' => 'API Health', 'icon' => 'bi-cpu']);
        })->name('api-health.index');

        Route::get('audit-trails', function () {
            return view('placeholders.module', ['title' => 'Audit Trails', 'icon' => 'bi-journal-text']);
        })->name('audit-trails.index');
    });
});
