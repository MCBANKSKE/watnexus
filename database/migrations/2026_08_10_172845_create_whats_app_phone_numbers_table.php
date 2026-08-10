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
        Schema::create('whatsapp_phone_numbers', function (Blueprint $table) {
            $table->id();

            // Company that owns the phone number
            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            // WhatsApp Business Account
            $table->foreignId('whatsapp_account_id')
                ->constrained('whatsapp_accounts')
                ->cascadeOnDelete();

            // Meta Phone Number ID
            $table->string('phone_number_id');

            // Actual phone number
            $table->string('phone_number');

            // Name displayed by WhatsApp
            $table->string('display_name')->nullable();

            // WhatsApp verification / connection status
            $table->enum('status', [
                'pending',
                'connected',
                'disconnected',
                'suspended',
            ])->default('pending');

            // Quality information from Meta
            $table->string('quality_rating')->nullable();

            // Messaging limits / tier information
            $table->string('messaging_limit')->nullable();

            // Country information
            $table->string('country_code', 10)->nullable();

            // Additional Meta information
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index('phone_number_id');
            $table->index('phone_number');
            $table->index('status');

            // A Meta phone number ID should be unique.
            $table->unique('phone_number_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_phone_numbers');
    }
};