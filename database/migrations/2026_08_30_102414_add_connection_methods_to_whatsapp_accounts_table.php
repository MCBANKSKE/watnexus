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
        Schema::table('whatsapp_accounts', function (Blueprint $table) {
            $table->enum('connection_method', ['manual', 'qr_code', 'oauth'])->default('manual')->after('status');
            $table->string('oauth_user_id')->nullable()->after('connection_method');
            $table->text('oauth_token')->nullable()->after('oauth_user_id');
            $table->json('qr_code_data')->nullable()->after('oauth_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whatsapp_accounts', function (Blueprint $table) {
            $table->dropColumn(['connection_method', 'oauth_user_id', 'oauth_token', 'qr_code_data']);
        });
    }
};
