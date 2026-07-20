<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RecalculateRiskJob;
use App\Jobs\SyncCountriesJob;
use App\Jobs\SyncExchangeJob;
use App\Jobs\SyncNewsJob;
use App\Jobs\SyncWeatherJob;
use App\Jobs\SyncWorldBankJob;
use App\Support\SyncTracker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SyncController extends Controller
{
    /**
     * Display the sync manager admin interface.
     */
    public function index()
    {
        $syncData = SyncTracker::all();

        return view('admin.sync.index', compact('syncData'));
    }

    /**
     * Trigger a manual synchronization for a specific service.
     */
    public function runSync(Request $request)
    {
        $request->validate([
            'service' => 'required|string|in:countries,ports,exchange,weather,worldbank,news,risk',
            'mode' => 'nullable|string|in:sync,queue',
        ]);

        $service = $request->input('service');
        $mode = $request->input('mode', 'sync');

        try {
            if ($mode === 'queue') {
                match ($service) {
                    'countries' => SyncCountriesJob::dispatch(),
                    'exchange' => SyncExchangeJob::dispatch(),
                    'weather' => SyncWeatherJob::dispatch(),
                    'worldbank' => SyncWorldBankJob::dispatch(),
                    'news' => SyncNewsJob::dispatch(),
                    'risk' => RecalculateRiskJob::dispatch(),
                    'ports' => Artisan::call('db:seed', ['--class' => 'WorldPortSeeder', '--force' => true]),
                };

                return redirect()->back()->with('status', "Queued synchronization job for service [{$service}] dispatch successfully!");
            }

            // Execute synchronously
            match ($service) {
                'countries' => Artisan::call('gscrip:sync-countries'),
                'ports' => Artisan::call('db:seed', ['--class' => 'WorldPortSeeder', '--force' => true]),
                'exchange' => Artisan::call('gscrip:sync-exchange'),
                'weather' => Artisan::call('gscrip:sync-weather'),
                'worldbank' => Artisan::call('gscrip:sync-worldbank'),
                'news' => Artisan::call('gscrip:sync-news'),
                'risk' => Artisan::call('gscrip:recalculate-risk'),
            };

            return redirect()->back()->with('status', "Synchronization for service [{$service}] completed successfully!");

        } catch (\Throwable $e) {
            return redirect()->back()->with('error', "Failed to run sync for [{$service}]: " . $e->getMessage());
        }
    }

    /**
     * Trigger complete full system setup & sync.
     */
    public function runAllSync()
    {
        try {
            Artisan::call('waypoint:setup');

            return redirect()->back()->with('status', "Full system setup and data synchronization completed successfully!");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', "System setup error: " . $e->getMessage());
        }
    }
}
