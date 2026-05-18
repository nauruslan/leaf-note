<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trashes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('capacity')->default(100);
            $table->unsignedInteger('current_quantity')->default(0);
            $table->string('auto_delete_days', 10)->nullable(); // Изменено на string согласно миграции 2026_04_16_070400
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trashes');
    }
};