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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();

            // Company that owns the conversation
            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            // WhatsApp number used by the company
            $table->foreignId('whatsapp_phone_number_id')
                ->constrained('whatsapp_phone_numbers')
                ->cascadeOnDelete();

            // Customer
            $table->foreignId('contact_id')
                ->constrained('contacts')
                ->cascadeOnDelete();

            // Conversation status
            $table->enum('status', [
                'open',
                'closed',
                'pending',
            ])->default('open');

            // Last message information
            $table->text('last_message')->nullable();
            $table->timestamp('last_message_at')->nullable();

            // Who sent the last message
            $table->enum('last_message_direction', [
                'inbound',
                'outbound',
            ])->nullable();

            // Unread messages for the company
            $table->unsignedInteger('unread_count')->default(0);

            // Optional assignment to a company user/agent
            $table->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Extra conversation information
            $table->json('metadata')->nullable();

            $table->timestamps();

            // A contact should have one conversation
            // per WhatsApp phone number.
            $table->unique([
                'whatsapp_phone_number_id',
                'contact_id',
            ]);

            $table->index('company_id');
            $table->index('status');
            $table->index('last_message_at');
            $table->index('assigned_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};