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
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('folder_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('trash_id')->nullable()->constrained();
            $table->foreignId('safe_id')->nullable()->constrained();
            $table->foreignId('archive_id')->nullable()->constrained();
            $table->timestamp('moved_to_trash_at')->nullable();
            $table->string('title');
            $table->string('type');
            $table->json('payload')->nullable();
            $table->string('color')->default('default');
            $table->boolean('is_favorite')->default(false);
            $table->timestamps();
            $table->index(['user_id', 'folder_id'], 'idx_notes_user_folder');
            $table->index(['user_id', 'trash_id'], 'idx_notes_user_trash');
            $table->index(['user_id', 'safe_id'], 'idx_notes_user_safe');
            $table->index(['user_id', 'archive_id'], 'idx_notes_user_archive');
            $table->index(['user_id', 'is_favorite'], 'idx_notes_favorite');
            $table->index(['user_id', 'updated_at'], 'idx_notes_user_updated');
            $table->index(['moved_to_trash_at', 'trash_id'], 'idx_notes_trash_cleanup');
            $table->unique(['user_id', 'title', 'folder_id'], 'unique_note_in_folder');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
