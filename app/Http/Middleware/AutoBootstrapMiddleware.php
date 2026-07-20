<?php

namespace App\Http\Middleware;

use App\Jobs\InitialSystemBootstrapJob;
use App\Models\Country;
use App\Models\SystemConfig;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class AutoBootstrapMiddleware
{
    /**
     * Handle an incoming request for automated system initialization.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip for console commands, static assets, build files, or health checks
        if (app()->runningInConsole() || $request->is('up', 'build/*', 'images/*')) {
            return $next($request);
        }

        // Fast-path: Check if database tables exist
        if (!Schema::hasTable('system_configs') || !Schema::hasTable('countries')) {
            return $next($request);
        }

        $isInitialized = SystemConfig::getByKey('system_initialized', false);
        $hasCountries = Country::count() > 0;

        // Fast-path: If database already has country data or initialized flag is true
        if ($isInitialized || $hasCountries) {
            if (!$isInitialized && $hasCountries) {
                SystemConfig::updateOrCreate(
                    ['key' => 'system_initialized'],
                    ['value' => 'true', 'type' => 'boolean', 'group' => 'system']
                );
            }
            Cache::forget('system_initialization_running');
            View::share('isSystemInitializing', false);
            return $next($request);
        }

        // System needs initial setup - Prevent duplicate triggers
        $isAlreadyRunning = Cache::has('system_initialization_running');

        if (!$isAlreadyRunning) {
            Cache::put('system_initialization_running', true, 600);

            // Local DX: In local environment or sync queue, execute bootstrap synchronously without requiring a queue worker
            if (app()->environment('local') || config('queue.default') === 'sync') {
                try {
                    InitialSystemBootstrapJob::dispatchSync();
                } catch (\Throwable $e) {
                    // Fail gracefully
                }
                Cache::forget('system_initialization_running');
                View::share('isSystemInitializing', false);
                return $next($request);
            }

            // Production: Dispatch to background queue worker
            InitialSystemBootstrapJob::dispatch();
        }

        View::share('isSystemInitializing', true);

        return $next($request);
    }
}
