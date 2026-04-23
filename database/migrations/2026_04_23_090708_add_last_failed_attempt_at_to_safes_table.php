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
        Schema::table('safes', function (Blueprint $table) {
            $table->timestamp('last_failed_attempt_at')->nullable()->after('failed_attempts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('safes', function (Blueprint $table) {
            $table->dropColumn('last_failed_attempt_at');
        });
    }
};