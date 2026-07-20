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
        // Skip for static assets, health checks, or console
        if (app()->runningInConsole() || $request->is('up', 'build/*', 'images/*')) {
            return $next($request);
        }

        // Fast-path: Check if database tables exist
        if (!Schema::hasTable('system_configs') || !Schema::hasTable('countries')) {
            return $next($request);
        }

        $isInitialized = SystemConfig::getByKey('system_initialized', false);
        $hasCountries = Country::count() > 0;

        if ($isInitialized && $hasCountries) {
            View::share('isSystemInitializing', false);
            return $next($request);
        }

        // System needs initialization - Check Atomic Lock
        $isAlreadyRunning = Cache::has('system_initialization_running');

        if (!$isAlreadyRunning) {
            // Acquire lock and dispatch background bootstrap job
            Cache::put('system_initialization_running', true, 600);
            InitialSystemBootstrapJob::dispatch();
        }

        View::share('isSystemInitializing', true);

        return $next($request);
    }
}
