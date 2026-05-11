<?php

namespace App\Services;

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
}