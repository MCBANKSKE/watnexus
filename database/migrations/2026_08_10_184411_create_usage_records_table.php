<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usage_records', function (Blueprint $table) {
            $table->id();

            // Company being charged/tracked
            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            // What was consumed
            $table->enum('type', [
                'message',
                'otp',
                'campaign_message',
                'api_request',
            ]);

            // Optional reference to the related record
            $table->unsignedBigInteger('reference_id')->nullable();

            // Quantity consumed
            $table->unsignedInteger('quantity')->default(1);

            // Optional unit cost
            $table->decimal('unit_price', 12, 4)->nullable();

            // Total calculated cost
            $table->decimal('total_price', 12, 4)->nullable();

            // Currency
            $table->string('currency', 3)->default('KES');

            // Period/date this usage belongs to
            $table->date('usage_date');

            // Additional information
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index('company_id');
            $table->index('type');
            $table->index('usage_date');
            $table->index([
                'reference_id',
                'type',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usage_records');
    }
};