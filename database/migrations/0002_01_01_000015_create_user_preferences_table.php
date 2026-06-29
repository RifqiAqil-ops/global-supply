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
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('dashboard_layout', 50)->default('default');
            $table->string('default_currency', 10)->default('USD');
            $table->string('default_region', 100)->nullable();
            $table->unsignedInteger('items_per_page')->default(25);
            $table->enum('theme', ['light', 'dark', 'auto'])->default('light');
            $table->boolean('email_notifications')->default(true);
            $table->boolean('alert_notifications')->default(true);
            $table->json('custom_settings')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
