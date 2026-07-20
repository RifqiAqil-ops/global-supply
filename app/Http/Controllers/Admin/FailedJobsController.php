<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FailedJobsController extends Controller
{
    /**
     * Display list of failed background queue jobs.
     */
    public function index()
    {
        $failedJobs = Schema::hasTable('failed_jobs')
            ? DB::table('failed_jobs')->orderBy('failed_at', 'desc')->paginate(15)
            : collect();

        return view('admin.operations.failed_jobs', compact('failedJobs'));
    }

    /**
     * Retry a specific failed job.
     */
    public function retry(string $id)
    {
        try {
            Artisan::call('queue:retry', ['id' => [$id]]);
            return redirect()->back()->with('status', "Failed job #{$id} has been queued for retry.");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', "Failed to retry job #{$id}: " . $e->getMessage());
        }
    }

    /**
     * Retry all failed jobs.
     */
    public function retryAll()
    {
        try {
            Artisan::call('queue:retry', ['id' => ['all']]);
            return redirect()->back()->with('status', "All failed queue jobs have been enqueued for retry.");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', "Failed to retry all jobs: " . $e->getMessage());
        }
    }

    /**
     * Delete a specific failed job.
     */
    public function destroy(string $id)
    {
        try {
            Artisan::call('queue:forget', ['id' => $id]);
            return redirect()->back()->with('status', "Failed job #{$id} deleted successfully.");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', "Failed to delete job #{$id}: " . $e->getMessage());
        }
    }

    /**
     * Flush all failed jobs.
     */
    public function flush()
    {
        try {
            Artisan::call('queue:flush');
            return redirect()->back()->with('status', "All failed jobs log cleared successfully.");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', "Failed to flush jobs: " . $e->getMessage());
        }
    }
}
