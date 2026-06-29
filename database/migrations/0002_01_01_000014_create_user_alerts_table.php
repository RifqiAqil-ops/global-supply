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
        Schema::create('user_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();
            $table->enum('alert_type', ['risk_score', 'weather_extreme', 'currency_change', 'news_sentiment']);
            $table->decimal('threshold_value', 10, 4)->nullable();
            $table->enum('comparison_operator', ['gt', 'gte', 'lt', 'lte', 'eq'])->default('gte');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('last_triggered_at')->nullable();
            $table->unsignedInteger('trigger_count')->default(0);
            $table->timestamps();

            $table->index('user_id');
            $table->index('country_id');
            $table->index('alert_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_alerts');
    }
};
