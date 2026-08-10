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
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();

            // Company that owns the API key
            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            // User who created the key
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Human-readable name
            $table->string('name');

            // Public identifier/prefix
            $table->string('key_prefix', 20);

            // Hashed API secret
            $table->string('key_hash');

            // Permissions/scopes
            $table->json('permissions')->nullable();

            // Status
            $table->boolean('is_active')->default(true);

            // Optional expiration
            $table->timestamp('expires_at')->nullable();

            // Usage information
            $table->timestamp('last_used_at')->nullable();

            // Optional IP restrictions
            $table->json('allowed_ips')->nullable();

            // Additional information
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index('company_id');
            $table->index('key_prefix');
            $table->index('is_active');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};