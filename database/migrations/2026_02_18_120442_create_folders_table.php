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
        Schema::create('folders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('title');
            $table->string('color')->default('default');
            $table->string('icon')->default('folder');

            $table->foreignId('trash_id')
                ->nullable()
                ->constrained();
            $table->timestamp('moved_to_trash_at')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'title', 'trash_id']);
            $table->index('trash_id', 'idx_folders_trash_id');
            $table->index('user_id', 'idx_folders_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('folders');
    }
};