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
        Schema::create('country_risk_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();
            $table->decimal('composite_score', 5, 2)->default(0)->index();
            $table->enum('risk_level', ['low', 'medium', 'high', 'critical'])->default('low')->index();
            $table->decimal('previous_score', 5, 2)->nullable();
            $table->decimal('score_change', 5, 2)->nullable();
            $table->decimal('data_completeness', 5, 2)->nullable();
            $table->date('score_date')->index();
            $table->timestamp('calculated_at')->useCurrent();
            $table->timestamps();

            $table->unique(['country_id', 'score_date'], 'country_risk_scores_country_date_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('country_risk_scores');
    }
};
