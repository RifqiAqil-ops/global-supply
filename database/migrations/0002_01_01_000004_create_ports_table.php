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
        Schema::create('ports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();
            $table->string('name');
            $table->string('port_code', 20)->nullable();
            $table->string('un_locode', 50)->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('port_type', 50)->default('sea');
            $table->string('port_size', 50)->nullable();
            $table->string('harbor_size', 50)->nullable();
            $table->string('harbor_type', 100)->nullable();
            $table->string('shelter', 50)->nullable();
            $table->unsignedInteger('max_vessel_length')->nullable();
            $table->string('max_vessel_size', 100)->nullable();
            $table->decimal('max_depth', 6, 2)->nullable();
            $table->string('region', 100)->nullable();
            $table->json('facilities')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('country_id');
            $table->index('port_type');
            $table->index('port_size');
            $table->index('region');
            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ports');
    }
};
