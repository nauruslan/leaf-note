<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trashes', function (Blueprint $table) {
            $table->string('auto_delete_days', 10)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('trashes', function (Blueprint $table) {
            $table->integer('auto_delete_days')->nullable()->change();
        });
    }
};