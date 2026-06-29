<?php

namespace App\Console\Commands;

use App\Http\Clients\TestApiClient;
use App\Models\ApiLog;
use Illuminate\Console\Command;
use Throwable;

class TestBaseClientCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gscrip:test-client';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the BaseApiClient integration and check database logs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Initializing TestApiClient with REST Countries...");
        $client = new TestApiClient();

        try {
            $this->info("Performing request to restcountries.com/v3.1/name/indonesia...");
            $response = $client->testFetch('indonesia');

            $this->info("Request succeeded!");
            
            if (isset($response[0]['name']['official'])) {
                $this->line("Official Country Name: <info>" . $response[0]['name']['official'] . "</info>");
            }

            // Retrieve the last api_log
            $log = ApiLog::latest('called_at')->first();

            if ($log) {
                $this->info("Retrieved last API log from database:");
                $this->table(
                    ['ID', 'Provider', 'Endpoint', 'Status', 'Latency', 'Success?'],
                    [[
                        $log->id,
                        $log->provider,
                        $log->endpoint,
                        $log->status_code,
                        $log->response_time . ' ms',
                        $log->is_success ? 'YES' : 'NO'
                    ]]
                );
            } else {
                $this->error("No API logs found in the database. Please verify the log configuration.");
            }

        } catch (Throwable $e) {
            $this->error("API client test failed with exception: " . class_basename($e));
            $this->line("Message: " . $e->getMessage());

            // Query log anyway
            $log = ApiLog::latest('called_at')->first();
            if ($log) {
                $this->error("Failure was logged to database: " . ($log->error_message ?? 'N/A'));
            }
        }

        return Command::SUCCESS;
    }
}
