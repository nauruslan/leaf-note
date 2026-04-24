<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notes', function ($table) {
            $table->dropUnique('unique_note_in_folder');
        });
    }

    public function down(): void
    {
        Schema::table('notes', function ($table) {
            $table->unique(['user_id', 'title', 'folder_id'], 'unique_note_in_folder');
        });
    }
};