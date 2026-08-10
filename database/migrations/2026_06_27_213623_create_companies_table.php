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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            // Basic company information
            $table->string('name');
            $table->string('slug')->unique();

            // Optional business information
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();

            // Company identification
            $table->string('registration_number')->nullable();
            $table->string('tax_number')->nullable();

            // Branding
            $table->string('logo')->nullable();

            // Address
            $table->text('address')->nullable();
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            
            // Company status
            $table->enum('status', [
                'active',
                'suspended',
                'pending',
            ])->default('active');

            // SaaS settings
            $table->string('timezone')->default('Africa/Nairobi');
            $table->foreignId('currency_id')->nullable()->constrained('countries')->nullOnDelete();
          
            // Additional flexible information
            $table->json('settings')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Useful indexes
            $table->index('status');
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};