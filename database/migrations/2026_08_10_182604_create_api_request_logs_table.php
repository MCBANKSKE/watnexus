<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_request_logs', function (Blueprint $table) {
            $table->id();

            // Company making the request
            $table->foreignId('company_id')
                ->nullable()
                ->constrained('companies')
                ->nullOnDelete();

            // API key used
            $table->foreignId('api_key_id')
                ->nullable()
                ->constrained('api_keys')
                ->nullOnDelete();

            // Request information
            $table->string('method', 10);
            $table->string('endpoint');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            // Request/response information
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->unsignedInteger('response_time_ms')->nullable();

            // Don't store sensitive information here.
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();

            // Error information
            $table->text('error_message')->nullable();

            $table->timestamps();

            $table->index('company_id');
            $table->index('api_key_id');
            $table->index('endpoint');
            $table->index('status_code');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_request_logs');
    }
};