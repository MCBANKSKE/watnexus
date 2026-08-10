<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_events', function (Blueprint $table) {
            $table->id();

            // Company this event belongs to
            $table->foreignId('company_id')
                ->nullable()
                ->constrained('companies')
                ->nullOnDelete();

            // WhatsApp account involved
            $table->foreignId('whatsapp_account_id')
                ->nullable()
                ->constrained('whatsapp_accounts')
                ->nullOnDelete();

            // Phone number involved
            $table->foreignId('whatsapp_phone_number_id')
                ->nullable()
                ->constrained('whatsapp_phone_numbers')
                ->nullOnDelete();

            // Meta event information
            $table->string('event_type');
            $table->string('event_id')->nullable();

            // Processing state
            $table->enum('status', [
                'received',
                'processing',
                'processed',
                'failed',
            ])->default('received');

            // Number of processing attempts
            $table->unsignedInteger('attempts')->default(0);

            // Raw webhook payload
            $table->json('payload');

            // Error information
            $table->text('error_message')->nullable();

            // Processing timestamps
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            $table->timestamps();

            $table->index('company_id');
            $table->index('whatsapp_account_id');
            $table->index('whatsapp_phone_number_id');
            $table->index('event_type');
            $table->index('status');
            $table->index('event_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_events');
    }
};