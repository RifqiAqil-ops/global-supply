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
        Schema::create('risk_score_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_risk_score_id')->constrained('country_risk_scores')->cascadeOnDelete();
            $table->foreignId('risk_category_id')->constrained('risk_categories')->cascadeOnDelete();
            $table->decimal('category_score', 5, 2)->default(0);
            $table->decimal('weighted_score', 5, 2)->default(0);
            $table->json('scoring_data')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(
                ['country_risk_score_id', 'risk_category_id'],
                'risk_details_score_category_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_score_details');
    }
};
