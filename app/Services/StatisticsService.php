<?php

namespace App\Services;

use App\Dto\UserStatisticsDto;
use App\Models\Note;
use App\Models\Folder;

/**
 * Сервис для получения статистики пользователя
 */
class StatisticsService
{
    /**
     * Получить статистику пользователя
     */
    public function getUserStatistics(int $userId): UserStatisticsDto
    {
        $notesCount = Note::where('user_id', $userId)
            ->where('type', Note::TYPE_NOTE)
            ->whereNull('trash_id')
            ->whereNull('archive_id')
            ->whereNull('safe_id')
            ->count();

        $checklistsCount = Note::where('user_id', $userId)
            ->where('type', Note::TYPE_CHECKLIST)
            ->whereNull('trash_id')
            ->whereNull('archive_id')
            ->whereNull('safe_id')
            ->count();

        $foldersCount = Folder::where('user_id', $userId)
            ->whereNull('trash_id')
            ->count();

        return new UserStatisticsDto(
            notesCount: $notesCount,
            checklistsCount: $checklistsCount,
            foldersCount: $foldersCount,
        );
    }
}