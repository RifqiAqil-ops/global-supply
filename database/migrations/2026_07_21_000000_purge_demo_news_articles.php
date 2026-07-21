<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Purge all legacy demo/fake news articles from database in production and local
        DB::table('news_articles')
            ->whereNull('source_url')
            ->orWhere('source_url', '')
            ->orWhere('source_url', 'like', '%example.com%')
            ->orWhere('source_url', 'like', '%gnews.io%')
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non-reversible
    }
};
