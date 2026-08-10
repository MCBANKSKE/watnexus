<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_contact', function (Blueprint $table) {
            $table->id();

            $table->foreignId('campaign_id')
                ->constrained('campaigns')
                ->cascadeOnDelete();

            $table->foreignId('contact_id')
                ->constrained('contacts')
                ->cascadeOnDelete();

            // Individual recipient status
            $table->enum('status', [
                'pending',
                'queued',
                'sent',
                'delivered',
                'read',
                'failed',
                'cancelled',
            ])->default('pending');

            // Message created for this recipient
            $table->foreignId('message_id')
                ->nullable()
                ->constrained('messages')
                ->nullOnDelete();

            // Individual timestamps
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            $table->text('error_message')->nullable();

            $table->timestamps();

            $table->unique([
                'campaign_id',
                'contact_id',
            ]);

            $table->index('status');
            $table->index('message_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_contact');
    }
};