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
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->string('currency_code', 10);
            $table->string('currency_name', 100)->nullable();
            $table->decimal('rate_to_usd', 20, 10);
            $table->decimal('rate_to_idr', 20, 4)->nullable();
            $table->decimal('change_percent', 8, 4)->nullable();
            $table->date('rate_date');
            $table->string('source', 100)->default('ExchangeRate API');
            $table->timestamps();

            $table->unique(['currency_code', 'rate_date'], 'exchange_rates_currency_date_unique');
            $table->index('currency_code');
            $table->index('rate_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
