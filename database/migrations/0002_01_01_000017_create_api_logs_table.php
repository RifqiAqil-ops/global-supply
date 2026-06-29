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
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 50);
            $table->string('endpoint', 500);
            $table->string('method', 10)->default('GET');
            $table->integer('status_code')->nullable();
            $table->decimal('response_time', 8, 2)->nullable();
            $table->json('request_params')->nullable();
            $table->unsignedInteger('response_size')->nullable();
            $table->text('error_message')->nullable();
            $table->boolean('is_success')->default(true);
            $table->timestamp('called_at')->useCurrent();
            $table->timestamp('created_at')->nullable();

            $table->index('provider');
            $table->index('is_success');
            $table->index('called_at');
            $table->index(['provider', 'called_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};
