<?php

namespace App\Console\Commands;

use App\Services\External\GNewsService;
use App\Support\SyncTracker;
use Illuminate\Console\Command;
use Throwable;

class SyncNewsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gscrip:sync-news {--force : Clear cache before syncing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize latest global supply chain news articles from GNews API';

    /**
     * Execute the console command.
     */
    public function handle(GNewsService $service)
    {
        $this->info("Initializing GNews news articles synchronization...");

        if ($this->option('force')) {
            $this->warn("Force mode active. Flushed news cache.");
            $service->flushCache();
        }

        if (!$service->hasApiKey()) {
            $this->warn("GNews API key is not configured. Real-time news fetch skipped.");
            $this->line("Please add GNEWS_API_KEY to your .env file to enable live feeds.");
            $this->line("Existing database articles remain available.");
            SyncTracker::success('news', microtime(true), 0);
            return Command::SUCCESS;
        }

        $this->info("Fetching articles for economic, geopolitical, and logistics topics...");
        $startTime = microtime(true);
        SyncTracker::start('news');

        try {
            $summary = $service->syncAllNews();

            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);
            $saved = $summary['saved'] ?? 0;

            SyncTracker::success('news', $startTime, $saved);

            $this->line("");
            $this->info("News synchronization completed in {$duration} seconds!");

            $this->table(
                ['Metric', 'Count'],
                [
                    ['Total Articles Fetched', $summary['fetched']],
                    ['New Articles Saved', $summary['saved']],
                    ['Duplicate Articles Skipped', $summary['duplicates']],
                    ['Failed Fetch/Parse Attempts', $summary['failed']],
                    ['Total News Articles in Database', \App\Models\NewsArticle::count()]
                ]
            );

        } catch (Throwable $e) {
            SyncTracker::fail('news', $startTime, $e);
            $this->error("Failed to run news synchronization: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
