<?php

namespace App\Services;

use App\Models\Archive;
use App\Models\Note;

class ArchiveService
{
    /**
     * Получить архив пользователя.
     */
    public function getUserArchive(int $userId): ?Archive
    {
        return Archive::where('user_id', $userId)->first();
    }

    /**
     * Получить ID архива пользователя.
     */
    public function getUserArchiveId(int $userId): ?int
    {
        return $this->getUserArchive($userId)?->id;
    }

    /**
     * Получить количество архивированных заметок пользователя.
     */
    public function getArchivedNotesCount(int $userId): int
    {
        return Note::forUser($userId)
            ->archived()
            ->count();
    }
}