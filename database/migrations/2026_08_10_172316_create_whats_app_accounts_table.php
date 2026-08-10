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
        Schema::create('whatsapp_accounts', function (Blueprint $table) {
            $table->id();

            // Company that owns this WhatsApp account
            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            // Meta / WhatsApp Business Account information
            $table->string('business_account_id')->nullable();
            $table->string('name')->nullable();

            // Connection status
            $table->enum('status', [
                'pending',
                'connected',
                'disconnected',
                'suspended',
            ])->default('pending');

            // Meta access credentials
            // These should be encrypted before being stored.
            $table->text('access_token')->nullable();

            // Optional token expiry information
            $table->timestamp('token_expires_at')->nullable();

            // Additional Meta information
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('business_account_id');
            $table->index('status');

            // A company should not have the same
            // WhatsApp Business Account twice.
            $table->unique([
                'company_id',
                'business_account_id'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_accounts');
    }
};