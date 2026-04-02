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
        Schema::table('trashes', function (Blueprint $table) {
            $table->integer('auto_delete_days')->nullable()->after('current_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trashes', function (Blueprint $table) {
            $table->dropColumn('auto_delete_days');
        });
    }
};
