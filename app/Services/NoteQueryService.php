<?php

namespace App\Services;

use App\Models\Note;
use Illuminate\Database\Eloquent\Builder;

class NoteQueryService
{
    /**
     * Получить количество заметок с применением скоупов.
     */
    public function countNotesWithScopes(int $userId, array $scopes = []): int
    {
        $query = Note::forUser($userId);

        foreach ($scopes as $scope) {
            if (method_exists($query->getModel(), 'scope' . ucfirst($scope))) {
                $query->$scope();
            }
        }

        return $query->count();
    }

    /**
     * Получить количество активных заметок.
     */
    public function getActiveNotesCount(int $userId): int
    {
        return $this->countNotesWithScopes($userId, ['active']);
    }

    /**
     * Получить количество избранных заметок.
     */
    public function getFavoriteNotesCount(int $userId): int
    {
        return $this->countNotesWithScopes($userId, ['favorite', 'active']);
    }

    /**
     * Получить количество заметок в папке.
     */
    public function getFolderNotesCount(int $userId, int $folderId): int
    {
        return Note::forUser($userId)
            ->where('folder_id', $folderId)
            ->count();
    }

    /**
     * Получить количество заметок в сейфе.
     */
    public function getSafeNotesCount(int $userId): int
    {
        return Note::forUser($userId)
            ->whereNotNull('safe_id')
            ->count();
    }

    /**
     * Получить базовый запрос заметок для пользователя.
     */
    public function getBaseQuery(int $userId): Builder
    {
        return Note::forUser($userId)->with('folder');
    }

    /**
     * Применить скоупы к запросу.
     */
    public function applyScopes(Builder $query, array $scopes): Builder
    {
        foreach ($scopes as $scope) {
            if (method_exists($query->getModel(), 'scope' . ucfirst($scope))) {
                $query->$scope();
            }
        }

        return $query;
    }
}