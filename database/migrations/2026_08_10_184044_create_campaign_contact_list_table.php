<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_contact_list', function (Blueprint $table) {
            $table->id();

            $table->foreignId('campaign_id')
                ->constrained('campaigns')
                ->cascadeOnDelete();

            $table->foreignId('contact_list_id')
                ->constrained('contact_lists')
                ->cascadeOnDelete();

            $table->timestamps();

            // Prevent attaching the same list
            // to the same campaign twice.
            $table->unique([
                'campaign_id',
                'contact_list_id',
            ]);

            $table->index('contact_list_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_contact_list');
    }
};