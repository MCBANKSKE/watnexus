<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_subscriptions', function (Blueprint $table) {
            $table->id();

            // Company using the subscription
            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            // Plan subscribed to
            $table->foreignId('plan_id')
                ->constrained('plans')
                ->restrictOnDelete();

            // Subscription status
            $table->enum('status', [
                'trialing',
                'active',
                'past_due',
                'cancelled',
                'expired',
            ])->default('active');

            // Subscription period
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();

            // Trial period
            $table->timestamp('trial_ends_at')->nullable();

            // Cancellation
            $table->timestamp('cancelled_at')->nullable();

            // When the subscription should renew
            $table->timestamp('next_billing_at')->nullable();

            // Price locked at subscription time
            $table->decimal('price', 12, 2);

            // Currency locked at subscription time
            $table->string('currency', 3)->default('KES');

            // Billing interval locked at subscription time
            $table->enum('billing_interval', [
                'monthly',
                'yearly',
            ])->default('monthly');

            // External payment provider information
            $table->string('provider')->nullable();
            $table->string('provider_subscription_id')->nullable();

            // Additional information
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index('company_id');
            $table->index('plan_id');
            $table->index('status');
            $table->index('ends_at');
            $table->index('next_billing_at');

            // A company can have multiple historical
            // subscriptions, but only one active/trialing
            // subscription should be enforced by application logic.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_subscriptions');
    }
};