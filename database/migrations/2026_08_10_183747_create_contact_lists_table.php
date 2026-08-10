<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_lists', function (Blueprint $table) {
            $table->id();

            // Company that owns the list
            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            // User who created the list
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // List information
            $table->string('name');
            $table->text('description')->nullable();

            // Whether the list is active
            $table->boolean('is_active')->default(true);

            // Cached number of contacts
            $table->unsignedInteger('contacts_count')->default(0);

            // Additional settings
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index('company_id');
            $table->index('is_active');

            // A company cannot have two lists
            // with the same name.
            $table->unique([
                'company_id',
                'name',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_lists');
    }
};