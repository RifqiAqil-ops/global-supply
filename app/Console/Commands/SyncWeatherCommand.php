<?php

namespace App\Console\Commands;

use App\Services\External\OpenMeteoService;
use App\Support\SyncTracker;
use Illuminate\Console\Command;
use Throwable;

class SyncWeatherCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gscrip:sync-weather {--force : Clear cache and refresh all weather data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize current weather forecast data from the Open-Meteo API';

    /**
     * Execute the console command.
     */
    public function handle(OpenMeteoService $service)
    {
        $this->info("Initializing Open-Meteo weather synchronization...");

        if ($this->option('force')) {
            $this->warn("Force mode active. Flushed existing weather cache.");
            $service->flushCache();
        }

        $this->info("Fetching batch coordinates weather forecast (50 locations per chunk)...");
        $startTime = microtime(true);
        SyncTracker::start('weather');

        try {
            $summary = $service->syncAllCountriesWeather();

            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);
            $processed = $summary['processed'] ?? 0;

            SyncTracker::success('weather', $startTime, $processed);

            $this->line("");
            $this->info("Weather synchronization completed in {$duration} seconds!");

            $this->table(
                ['Metric', 'Count'],
                [
                    ['Total Processed Countries', $summary['processed']],
                    ['New Weather Snapshots Created', $summary['new']],
                    ['Weather Records Updated', $summary['updated']],
                    ['Failed Sync Attempts', $summary['failed']],
                    ['Total Weather Entries in DB', \App\Models\WeatherData::count()]
                ]
            );

        } catch (Throwable $e) {
            SyncTracker::fail('weather', $startTime, $e);
            $this->error("Failed to run weather synchronization: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
