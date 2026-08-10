<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            // Customer details
            $table->string('name')->nullable();
            $table->string('phone');
            $table->string('email')->nullable();

            // WhatsApp profile name
            $table->string('whatsapp_name')->nullable();

            // Status
            $table->enum('status', [
                'active',
                'blocked',
                'archived',
            ])->default('active');

            // Optional segmentation
            $table->string('country_code', 10)->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Same company cannot save the same phone twice
            $table->unique(['company_id', 'phone']);

            $table->index('phone');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};