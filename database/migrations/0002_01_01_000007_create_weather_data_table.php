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
        Schema::create('weather_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();
            $table->string('city_name')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('temperature', 5, 2)->nullable();
            $table->decimal('feels_like', 5, 2)->nullable();
            $table->decimal('humidity', 5, 2)->nullable();
            $table->decimal('wind_speed', 6, 2)->nullable();
            $table->unsignedInteger('wind_direction')->nullable();
            $table->decimal('precipitation', 6, 2)->nullable();
            $table->decimal('pressure', 7, 2)->nullable();
            $table->decimal('visibility', 8, 2)->nullable();
            $table->decimal('uv_index', 4, 2)->nullable();
            $table->integer('weather_code')->nullable();
            $table->string('weather_description')->nullable();
            $table->boolean('is_extreme')->default(false)->index();
            $table->json('daily_forecast')->nullable();
            $table->timestamp('fetched_at')->useCurrent();
            $table->timestamps();

            $table->index('country_id');
            $table->index('fetched_at');
            $table->index(['country_id', 'fetched_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weather_data');
    }
};
