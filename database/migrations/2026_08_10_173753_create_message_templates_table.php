<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();

            // Company that owns the template
            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            // Meta/WhatsApp template ID
            $table->string('whatsapp_template_id')
                ->nullable();

            // Template name used by WhatsApp
            $table->string('name');

            // Template category
            $table->enum('category', [
                'authentication',
                'utility',
                'marketing',
            ]);

            // Language code
            $table->string('language', 10)->default('en');

            // Template status from WhatsApp
            $table->enum('status', [
                'draft',
                'pending',
                'approved',
                'rejected',
                'paused',
                'disabled',
            ])->default('draft');

            // Template body
            $table->longText('body');

            // Header configuration
            $table->json('header')->nullable();

            // Footer text
            $table->text('footer')->nullable();

            // Buttons
            $table->json('buttons')->nullable();

            // Variables/placeholders
            $table->json('variables')->nullable();

            // Meta rejection information
            $table->text('rejection_reason')->nullable();

            // Synchronization information
            $table->timestamp('synced_at')->nullable();

            // Additional information
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index('company_id');
            $table->index('whatsapp_template_id');
            $table->index('status');
            $table->index('category');

            $table->unique([
                'company_id',
                'name',
                'language',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_templates');
    }
};