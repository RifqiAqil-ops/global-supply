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
        Schema::create('economic_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();
            $table->string('indicator_code', 50);
            $table->string('indicator_name');
            $table->year('year');
            $table->decimal('value', 20, 4)->nullable();
            $table->string('unit', 50)->nullable();
            $table->string('source', 100)->default('World Bank');
            $table->timestamps();

            $table->unique(['country_id', 'indicator_code', 'year'], 'economic_country_indicator_year_unique');
            $table->index('indicator_code');
            $table->index('year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('economic_indicators');
    }
};
