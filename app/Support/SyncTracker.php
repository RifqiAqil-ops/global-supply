<?php

namespace App\Support;

use App\Models\SystemConfig;
use Illuminate\Support\Facades\Log;

class SyncTracker
{
    /**
     * Start a sync process log entry.
     */
    public static function start(string $service): array
    {
        $info = [
            'service' => $service,
            'status' => 'syncing',
            'started_at' => now()->toDateTimeString(),
            'last_sync_at' => now()->toDateTimeString(),
            'next_scheduled_at' => static::calculateNextScheduled($service),
            'records_updated' => 0,
            'duration_seconds' => 0,
            'memory_usage_mb' => static::getMemoryUsage(),
            'peak_memory_mb' => static::getPeakMemory(),
            'http_status' => 200,
            'api_latency_ms' => 0,
            'retry_count' => 0,
            'error_message' => null,
        ];

        static::saveInfo($service, $info);

        return ['start_time' => microtime(true), 'info' => $info];
    }

    /**
     * Record a successful sync completion.
     */
    public static function success(string $service, float $startTime, int $recordsUpdated = 0, int $httpStatus = 200): array
    {
        $duration = round(microtime(true) - $startTime, 2);
        $latencyMs = round($duration * 1000, 2);

        $info = [
            'service' => $service,
            'status' => 'success',
            'last_sync_at' => now()->toDateTimeString(),
            'next_scheduled_at' => static::calculateNextScheduled($service),
            'records_updated' => $recordsUpdated,
            'duration_seconds' => $duration,
            'memory_usage_mb' => static::getMemoryUsage(),
            'peak_memory_mb' => static::getPeakMemory(),
            'http_status' => $httpStatus,
            'api_latency_ms' => $latencyMs,
            'retry_count' => 0,
            'error_message' => null,
        ];

        static::saveInfo($service, $info);

        Log::info("SyncTracker: Service [{$service}] completed successfully in {$duration}s with {$recordsUpdated} records.");

        return $info;
    }

    /**
     * Record a failed sync attempt.
     */
    public static function fail(string $service, float $startTime, \Throwable|string $error, int $httpStatus = 500): array
    {
        $duration = round(microtime(true) - $startTime, 2);
        $latencyMs = round($duration * 1000, 2);
        $errorMessage = is_string($error) ? $error : $error->getMessage();

        $info = [
            'service' => $service,
            'status' => 'failed',
            'last_sync_at' => now()->toDateTimeString(),
            'next_scheduled_at' => static::calculateNextScheduled($service),
            'records_updated' => 0,
            'duration_seconds' => $duration,
            'memory_usage_mb' => static::getMemoryUsage(),
            'peak_memory_mb' => static::getPeakMemory(),
            'http_status' => $httpStatus,
            'api_latency_ms' => $latencyMs,
            'retry_count' => 1,
            'error_message' => $errorMessage,
        ];

        static::saveInfo($service, $info);

        Log::error("SyncTracker: Service [{$service}] failed after {$duration}s: {$errorMessage}");

        return $info;
    }

    /**
     * Get sync info for a service.
     */
    public static function get(string $service): array
    {
        $key = "sync_info_{$service}";
        $raw = SystemConfig::getByKey($key);

        if (is_array($raw)) {
            return static::normalizeInfo($service, $raw);
        }

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return static::normalizeInfo($service, $decoded);
            }
        }

        return [
            'service' => $service,
            'status' => 'pending',
            'last_sync_at' => null,
            'next_scheduled_at' => static::calculateNextScheduled($service),
            'records_updated' => 0,
            'duration_seconds' => 0,
            'memory_usage_mb' => 0,
            'peak_memory_mb' => 0,
            'http_status' => 200,
            'api_latency_ms' => 0,
            'retry_count' => 0,
            'error_message' => null,
        ];
    }

    /**
     * Get status for all services.
     */
    public static function all(): array
    {
        $services = ['countries', 'ports', 'exchange', 'weather', 'worldbank', 'news', 'risk'];
        $result = [];

        foreach ($services as $s) {
            $result[$s] = static::get($s);
        }

        return $result;
    }

    /**
     * Save info to system_configs table.
     */
    protected static function saveInfo(string $service, array $info): void
    {
        $key = "sync_info_{$service}";

        SystemConfig::updateOrCreate(
            ['key' => $key],
            [
                'value' => json_encode($info),
                'type' => 'json',
                'group' => 'sync',
                'label' => "Sync info for {$service}",
                'description' => 'Automated tracker log metadata for synchronization tasks',
                'is_editable' => false,
            ]
        );
    }

    protected static function normalizeInfo(string $service, array $info): array
    {
        return array_merge([
            'service' => $service,
            'status' => 'pending',
            'last_sync_at' => null,
            'next_scheduled_at' => static::calculateNextScheduled($service),
            'records_updated' => 0,
            'duration_seconds' => 0,
            'memory_usage_mb' => static::getMemoryUsage(),
            'peak_memory_mb' => static::getPeakMemory(),
            'http_status' => 200,
            'api_latency_ms' => 0,
            'retry_count' => 0,
            'error_message' => null,
        ], $info);
    }

    public static function calculateNextScheduled(string $service): string
    {
        $now = now();
        return match ($service) {
            'countries' => $now->addWeek()->toDateTimeString(),
            'ports' => 'On Setup / Manual',
            'exchange' => $now->addHour()->toDateTimeString(),
            'weather' => $now->addMinutes(30)->toDateTimeString(),
            'worldbank' => $now->addDay()->toDateTimeString(),
            'news' => $now->addHour()->toDateTimeString(),
            'risk' => $now->addHour()->toDateTimeString(),
            default => $now->addHour()->toDateTimeString(),
        };
    }

    protected static function getMemoryUsage(): float
    {
        return round(memory_get_usage(true) / 1024 / 1024, 2);
    }

    protected static function getPeakMemory(): float
    {
        return round(memory_get_peak_usage(true) / 1024 / 1024, 2);
    }
}
