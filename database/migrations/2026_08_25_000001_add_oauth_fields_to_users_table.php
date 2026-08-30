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
        Schema::table('users', function (Blueprint $table) {
            // OAuth identity columns used for "Sign in with Google" (and future
            // social providers). `provider` records the source, `google_id` is
            // the unique remote PK, and `avatar` caches the Google picture URL.
            $table->string('provider')->default('native')->after('password');
            $table->string('google_id')->nullable()->after('provider');
            $table->string('avatar')->nullable()->after('google_id');

            // A given Google ID must be unique to a single user. MySQL permits
            // multiple NULL values in a UNIQUE column, so password-only users
            // are unaffected.
            $table->unique('google_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['google_id']);
            $table->dropColumn(['provider', 'google_id', 'avatar']);
        });
    }
};
