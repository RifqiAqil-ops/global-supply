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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->char('iso2', 2)->unique();
            $table->char('iso3', 3)->unique();
            $table->string('name')->index();
            $table->string('official_name')->nullable();
            $table->string('capital')->nullable();
            $table->string('region', 100)->nullable()->index();
            $table->string('sub_region', 100)->nullable()->index();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->bigInteger('population')->unsigned()->default(0);
            $table->decimal('area', 15, 2)->nullable();
            $table->string('flag_url', 500)->nullable();
            $table->string('flag_emoji', 10)->nullable();
            $table->string('currency_code', 10)->nullable()->index();
            $table->string('currency_name', 100)->nullable();
            $table->string('currency_symbol', 10)->nullable();
            $table->string('calling_code', 20)->nullable();
            $table->string('tld', 10)->nullable();
            $table->json('timezones')->nullable();
            $table->json('languages')->nullable();
            $table->json('borders')->nullable();
            $table->boolean('is_independent')->default(true);
            $table->boolean('is_un_member')->default(false);
            $table->timestamps();

            $table->fullText(['name', 'official_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
