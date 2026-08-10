<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_statuses', function (Blueprint $table) {
            $table->id();

            // Message whose status changed
            $table->foreignId('message_id')
                ->constrained('messages')
                ->cascadeOnDelete();

            // WhatsApp message ID
            $table->string('whatsapp_message_id')->nullable();

            // Status received from WhatsApp
            $table->enum('status', [
                'queued',
                'sending',
                'sent',
                'delivered',
                'read',
                'failed',
            ]);

            // Error details if the status is failed
            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();

            // When WhatsApp reported the status
            $table->timestamp('occurred_at')->nullable();

            // Raw webhook information related to this status
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index('message_id');
            $table->index('whatsapp_message_id');
            $table->index('status');
            $table->index('occurred_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_statuses');
    }
};