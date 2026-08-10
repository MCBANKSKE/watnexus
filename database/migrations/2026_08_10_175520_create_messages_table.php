<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->foreignId('conversation_id')
                ->constrained('conversations')
                ->cascadeOnDelete();

            $table->foreignId('whatsapp_phone_number_id')
                ->constrained('whatsapp_phone_numbers')
                ->cascadeOnDelete();

            $table->foreignId('contact_id')
                ->nullable()
                ->constrained('contacts')
                ->nullOnDelete();

            $table->foreignId('message_template_id')
                ->nullable()
                ->constrained('message_templates')
                ->nullOnDelete();

            // Message direction
            $table->enum('direction', [
                'outbound',
                'inbound',
            ]);

            // Type of WhatsApp message
            $table->enum('type', [
                'text',
                'template',
                'image',
                'video',
                'audio',
                'document',
                'location',
                'interactive',
                'sticker',
            ])->default('text');

            // Message processing status
            $table->enum('status', [
                'queued',
                'sending',
                'sent',
                'delivered',
                'read',
                'failed',
            ])->default('queued');

            // WhatsApp / Meta message ID
            $table->string('whatsapp_message_id')
                ->nullable()
                ->unique();

            // Message content
            $table->longText('body')->nullable();

            // Media information
            $table->string('media_url')->nullable();
            $table->string('media_type')->nullable();
            $table->string('media_filename')->nullable();

            // Error information
            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();

            // Additional WhatsApp data
            $table->json('metadata')->nullable();

            // Message timestamps
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            $table->timestamps();

            $table->index('direction');
            $table->index('type');
            $table->index('status');
            $table->index('company_id');
            $table->index('conversation_id');
            $table->index('contact_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};