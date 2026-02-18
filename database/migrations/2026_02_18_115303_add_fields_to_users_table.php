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
            // Добавляем новые поля в нужном порядке
            $table->boolean('is_demo')->nullable()->default(false)->after('password');
            $table->string('avatar_path')->nullable()->after('is_demo');
            $table->string('gender')->nullable()->after('avatar_path');
            $table->date('birth_date')->nullable()->after('gender');
            $table->string('country')->nullable()->after('birth_date');
            $table->string('google_id')->nullable()->unique()->after('country');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Удаляем поля в обратном порядке
            $table->dropColumn(['google_id', 'country', 'birth_date', 'gender', 'avatar_path', 'is_demo']);
        });
    }
};