<?php

namespace App\Services;

use App\Dto\UserStatisticsDto;
use App\Dto\SidebarStatisticsDto;
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

    /**
     * Получить статистику заметок для сайдбара
     */
    public function getNoteCounts(int $userId): SidebarStatisticsDto
    {
        $counts = Note::where('user_id', $userId)
            ->selectRaw("
                COUNT(CASE WHEN trash_id IS NULL AND archive_id IS NULL AND safe_id IS NULL THEN 1 END) as dashboard,
                COUNT(CASE WHEN safe_id IS NOT NULL THEN 1 END) as safe,
                COUNT(CASE WHEN archive_id IS NOT NULL THEN 1 END) as archive,
                COUNT(CASE WHEN is_favorite = 1 AND trash_id IS NULL AND archive_id IS NULL AND safe_id IS NULL THEN 1 END) as favorite
            ")
            ->first();

        return new SidebarStatisticsDto(
            dashboard: $counts->dashboard ?? 0,
            safe: $counts->safe ?? 0,
            archive: $counts->archive ?? 0,
            favorite: $counts->favorite ?? 0,
        );
    }

    /**
     * Получить количество элементов в корзине
     */
    public function getTrashCount(int $userId): int
    {
        $notesCount = Note::where('user_id', $userId)
            ->whereNotNull('trash_id')
            ->count();

        $foldersCount = Folder::where('user_id', $userId)
            ->whereNotNull('trash_id')
            ->count();

        return $notesCount + $foldersCount;
    }
}