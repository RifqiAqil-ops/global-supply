<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('news_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->string('title', 500);
            $table->text('description')->nullable();
            $table->text('content')->nullable();
            $table->string('source_name')->nullable();
            $table->string('source_url', 1000)->nullable();
            $table->string('image_url', 1000)->nullable();
            $table->enum('category', ['economic', 'geopolitical', 'logistics', 'general'])->default('general');
            $table->enum('sentiment', ['positive', 'negative', 'neutral'])->default('neutral');
            $table->string('search_query')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('fetched_at')->useCurrent();
            $table->timestamps();

            $table->index('country_id');
            $table->index('category');
            $table->index('sentiment');
            $table->index('published_at');
            $table->index('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_articles');
    }
};
