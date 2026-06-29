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
        Schema::create('risk_weights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('risk_category_id')->unique()->constrained('risk_categories')->cascadeOnDelete();
            $table->decimal('weight', 5, 4)->default(0.2000);
            $table->decimal('min_threshold', 5, 2)->default(0);
            $table->decimal('max_threshold', 5, 2)->default(100);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_weights');
    }
};
