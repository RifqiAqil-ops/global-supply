<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiLog;
use App\Support\SyncTracker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class ObservabilityController extends Controller
{
    /**
     * Display the Enterprise Observability Dashboard.
     */
    public function index()
    {
        $syncData = SyncTracker::all();

        // Calculate Success vs Failure Rates
        $totalSyncs = count($syncData);
        $successCount = 0;
        $failedCount = 0;
        $totalDuration = 0;
        $totalRecords = 0;

        foreach ($syncData as $info) {
            if (($info['status'] ?? '') === 'success') {
                $successCount++;
            } elseif (($info['status'] ?? '') === 'failed') {
                $failedCount++;
            }

            $totalDuration += ($info['duration_seconds'] ?? 0);
            $totalRecords += ($info['records_updated'] ?? 0);
        }

        $successRate = $totalSyncs > 0 ? round(($successCount / $totalSyncs) * 100, 1) : 100;
        $failureRate = $totalSyncs > 0 ? round(($failedCount / $totalSyncs) * 100, 1) : 0;
        $avgSyncDuration = $totalSyncs > 0 ? round($totalDuration / $totalSyncs, 2) : 0;

        // Health Checks
        $dbStart = microtime(true);
        try {
            DB::connection()->getPdo();
            $dbLatency = round((microtime(true) - $dbStart) * 1000, 2);
            $dbStatus = 'Healthy';
        } catch (\Throwable $e) {
            $dbLatency = 0;
            $dbStatus = 'Offline';
        }

        $cacheStart = microtime(true);
        try {
            Cache::put('obs_test', 'ok', 5);
            $cacheLatency = round((microtime(true) - $cacheStart) * 1000, 2);
            $cacheStatus = 'Healthy';
        } catch (\Throwable $e) {
            $cacheLatency = 0;
            $cacheStatus = 'Offline';
        }

        // Memory Usage
        $memoryUsage = round(memory_get_usage(true) / 1024 / 1024, 2);
        $peakMemory = round(memory_get_peak_usage(true) / 1024 / 1024, 2);

        // Queue Throughput
        $pendingJobs = Schema::hasTable('jobs') ? DB::table('jobs')->count() : 0;
        $failedJobs = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0;

        // Disk Usage
        $freeDisk = function_exists('disk_free_space') ? round(disk_free_space(base_path()) / 1024 / 1024 / 1024, 2) : 'N/A';
        $totalDisk = function_exists('disk_total_space') ? round(disk_total_space(base_path()) / 1024 / 1024 / 1024, 2) : 'N/A';

        return view('admin.observability.index', compact(
            'syncData',
            'successRate',
            'failureRate',
            'avgSyncDuration',
            'totalRecords',
            'dbStatus',
            'dbLatency',
            'cacheStatus',
            'cacheLatency',
            'memoryUsage',
            'peakMemory',
            'pendingJobs',
            'failedJobs',
            'freeDisk',
            'totalDisk'
        ));
    }
}
