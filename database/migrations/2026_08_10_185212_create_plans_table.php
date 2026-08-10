<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();

            // Internal unique plan name
            $table->string('name');

            // Human-readable name
            $table->string('display_name');

            // Description shown to customers
            $table->text('description')->nullable();

            // Monthly price
            $table->decimal('price', 12, 2)->default(0);

            // Currency
            $table->string('currency', 3)->default('KES');

            // Billing interval
            $table->enum('billing_interval', [
                'monthly',
                'yearly',
            ])->default('monthly');

            // Platform limits
            $table->unsignedInteger('messages_limit')->nullable();
            $table->unsignedInteger('otp_limit')->nullable();
            $table->unsignedInteger('api_requests_limit')->nullable();
            $table->unsignedInteger('campaigns_limit')->nullable();
            $table->unsignedInteger('contacts_limit')->nullable();
            $table->unsignedInteger('users_limit')->nullable();
            $table->unsignedInteger('whatsapp_numbers_limit')->nullable();

            // Feature configuration
            $table->json('features')->nullable();

            // Whether customers can currently subscribe
            $table->boolean('is_active')->default(true);

            // Whether this is the default/free plan
            $table->boolean('is_default')->default(false);

            // Display ordering
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->unique('name');

            $table->index('is_active');
            $table->index('is_default');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};