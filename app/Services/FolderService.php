<?php

namespace App\Services;

use App\Dto\CreateFolderDto;
use App\Dto\UpdateFolderDto;
use App\Models\Folder;
use App\Models\Note;

class FolderService
{
    /**
     * Получить папку пользователя по ID.
     */
    public function getFolder(int $userId, int $folderId): ?Folder
    {
        return Folder::where('user_id', $userId)
            ->where('id', $folderId)
            ->active()
            ->first();
    }

    /**
     * Получить количество заметок в папке.
     */
    public function getNotesCount(int $userId, int $folderId): int
    {
        return Note::forUser($userId)
            ->where('folder_id', $folderId)
            ->count();
    }

    /**
     * Удалить папку (переместить в корзину).
     */
    public function deleteFolder(int $userId, int $folderId): array
    {
        $folder = $this->getFolder($userId, $folderId);

        if (!$folder) {
            return [
                'success' => false,
                'message' => 'Папка не найдена',
            ];
        }

        $success = $folder->moveToTrash();

        if ($success) {
            return [
                'success' => true,
                'message' => "Папка «{$folder->title}» отправлена в корзину",
            ];
        }

        return [
            'success' => false,
            'message' => 'Корзина переполнена. Очистите корзину перед удалением.',
        ];
    }

    /**
     * Создать папку.
     */
    public function createFolder(CreateFolderDto $dto): Folder
    {
        $folder = new Folder();
        $folder->title = $dto->title;
        $folder->color = $dto->color;
        $folder->icon = $dto->icon;
        $folder->user_id = $dto->userId;
        $folder->save();

        return $folder;
    }

    /**
     * Обновить папку.
     */
    public function updateFolder(UpdateFolderDto $dto): Folder
    {
        $folder = $this->getFolder($dto->userId, $dto->folderId);

        if (!$folder) {
            throw new \InvalidArgumentException('Папка не найдена');
        }

        $folder->title = $dto->title;
        $folder->color = $dto->color;
        $folder->icon = $dto->icon;
        $folder->save();

        return $folder;
    }

    /**
     * Проверить существование папки с указанным названием.
     */
    public function isTitleExists(int $userId, string $title, ?int $excludeFolderId = null): bool
    {
        $query = Folder::where('user_id', $userId)
            ->where('title', $title)
            ->whereNull('trash_id');

        if ($excludeFolderId) {
            $query->where('id', '!=', $excludeFolderId);
        }

        return $query->exists();
    }

    /**
     * Проверить существование папки с указанной иконкой.
     */
    public function isIconExists(int $userId, string $icon, ?int $excludeFolderId = null): bool
    {
        $query = Folder::where('user_id', $userId)
            ->where('icon', $icon)
            ->whereNull('trash_id');

        if ($excludeFolderId) {
            $query->where('id', '!=', $excludeFolderId);
        }

        return $query->exists();
    }

    /**
     * Получить занятые иконки пользователя.
     */
    public function getUsedIcons(int $userId, ?int $excludeFolderId = null): array
    {
        $query = Folder::where('user_id', $userId)
            ->whereNull('trash_id');

        if ($excludeFolderId) {
            $query->where('id', '!=', $excludeFolderId);
        }

        return $query->pluck('icon')->toArray();
    }

    /**
     * Получить список доступных иконок.
     */
    public function getIcons(): array
    {
        return Folder::ICONS;
    }

    /**
     * Получить папки с количеством активных заметок для сайдбара
     */
    public function getFoldersWithNotesCount(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return Folder::where('user_id', $userId)
            ->active()
            ->orderBy('title')
            ->withCount(['activeNotes as notes_count' => function ($query) {
                $query->whereNull('trash_id')
                      ->whereNull('archive_id')
                      ->whereNull('safe_id');
            }])
            ->get();
    }

    /**
     * Получить активные папки пользователя для dropdown.
     */
    public function getActiveFolders(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return Folder::where('user_id', $userId)
            ->active()
            ->orderBy('title')
            ->get();
    }
}
