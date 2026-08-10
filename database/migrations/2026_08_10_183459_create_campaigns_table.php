<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();

            // Company that owns the campaign
            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            // User who created the campaign
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Campaign name
            $table->string('name');

            // Optional description
            $table->text('description')->nullable();

            // Template used by the campaign
            $table->foreignId('message_template_id')
                ->nullable()
                ->constrained('message_templates')
                ->nullOnDelete();

            // Campaign status
            $table->enum('status', [
                'draft',
                'scheduled',
                'running',
                'paused',
                'completed',
                'cancelled',
                'failed',
            ])->default('draft');

            // Scheduling
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Campaign statistics
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedInteger('queued_count')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('delivered_count')->default(0);
            $table->unsignedInteger('read_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);

            // Additional settings
            $table->json('settings')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index('company_id');
            $table->index('status');
            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};