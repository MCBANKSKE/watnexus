<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_list_contact', function (Blueprint $table) {
            $table->id();

            $table->foreignId('contact_list_id')
                ->constrained('contact_lists')
                ->cascadeOnDelete();

            $table->foreignId('contact_id')
                ->constrained('contacts')
                ->cascadeOnDelete();

            $table->timestamps();

            // Prevent the same contact from being
            // added to the same list twice.
            $table->unique([
                'contact_list_id',
                'contact_id',
            ]);

            $table->index('contact_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_list_contact');
    }
};