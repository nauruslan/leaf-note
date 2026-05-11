<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Сервис для запросов к данным корзины
 */
class TrashQueryService
{
    /**
     * Получить удалённые заметки с пагинацией
     */
    public function getTrashedNotes(
        int $userId,
        ?string $search = null,
        ?string $filter = null,
        string $sort = 'deleted',
        int $page = 1,
        int $perPage = 10,
    ): LengthAwarePaginator {
        $sortMap = [
            'deleted' => 'moved_to_trash_at',
            'title' => 'title',
        ];
        $sortDirections = [
            'deleted' => 'desc',
            'title' => 'asc',
        ];

        $query = \App\Models\Note::where('user_id', $userId)
            ->whereNotNull('trash_id')
            ->whereNull('folder_id')
            ->with('folder');

        // Применяем фильтр по типу (при поиске показываем все типы)
        $isSearching = strlen(trim($search ?? '')) > 0;
        if (!$isSearching && $filter) {
            if ($filter === 'notes') {
                $query->where('type', \App\Models\Note::TYPE_NOTE);
            } elseif ($filter === 'checklists') {
                $query->where('type', \App\Models\Note::TYPE_CHECKLIST);
            }
        }

        // Применяем поиск
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('search_content', 'like', "%{$search}%");
            });
        }

        // Применяем сортировку
        $sortColumn = $sortMap[$sort] ?? 'moved_to_trash_at';
        $sortDirection = $sortDirections[$sort] ?? 'desc';
        $query->orderBy($sortColumn, $sortDirection);

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Получить удалённые папки
     */
    public function getTrashedFolders(
        int $userId,
        ?string $search = null,
        string $sort = 'deleted',
    ): Collection {
        $sortMap = [
            'deleted' => 'moved_to_trash_at',
            'title' => 'title',
        ];
        $sortDirections = [
            'deleted' => 'desc',
            'title' => 'asc',
        ];

        $query = \App\Models\Folder::where('user_id', $userId)
            ->whereNotNull('trash_id');

        // Применяем поиск
        if ($search) {
            $query->where('title', 'like', "%{$search}%");
        }

        // Применяем сортировку
        $sortColumn = $sortMap[$sort] ?? 'moved_to_trash_at';
        $sortDirection = $sortDirections[$sort] ?? 'desc';
        $query->orderBy($sortColumn, $sortDirection);

        return $query->get();
    }
}