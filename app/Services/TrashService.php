<?php

namespace App\Services;

use App\Dto\DeleteResultDto;
use App\Dto\RestoreResultDto;
use App\Dto\TrashSettingsDto;
use App\Models\Folder;
use App\Models\Note;
use App\Models\Trash;
use Illuminate\Database\Eloquent\Collection;

/**
 * Сервис для управления корзиной
 */
class TrashService
{
    /**
     * Получить настройку автоудаления из корзины
     */
    public function getAutoDeleteDays(int $userId): string
    {
        $trash = Trash::where('user_id', $userId)->first();
        if (!$trash) {
            return 'disabled';
        }

        $days = $trash->auto_delete_days ?? null;
        return $days ? (string) $days : 'disabled';
    }

    /**
     * Обновить настройку автоудаления
     */
    public function updateAutoDeleteDays(int $userId, TrashSettingsDto $dto): void
    {
        $trash = Trash::where('user_id', $userId)->first();
        if (!$trash) {
            return;
        }

        if ($dto->autoDeleteDays === 'disabled') {
            $trash->auto_delete_days = null;
        } elseif ($dto->autoDeleteDays === '1min') {
            $trash->auto_delete_days = '1min';
        } else {
            $trash->auto_delete_days = (int) $dto->autoDeleteDays;
        }

        $trash->save();
    }

    /**
     * Восстановить заметку из корзины
     */
    public function restoreNote(int $userId, int $noteId): RestoreResultDto
    {
        $note = Note::where('user_id', $userId)->find($noteId);

        if (!$note || !$note->isInTrash()) {
            return new RestoreResultDto(
                success: false,
                message: 'Заметка не найдена или не в корзине',
            );
        }

        $note->restoreFromTrash();

        $typeLabel = $note->type === Note::TYPE_NOTE ? 'Заметка' : 'Список';

        return new RestoreResultDto(
            success: true,
            message: "{$typeLabel} «{$note->title}» помещена в Архив",
            location: 'archive',
        );
    }

    /**
     * Восстановить папку из корзины
     */
    public function restoreFolder(int $userId, int $folderId): RestoreResultDto
    {
        $folder = Folder::where('user_id', $userId)->find($folderId);

        if (!$folder || !$folder->isInTrash()) {
            return new RestoreResultDto(
                success: false,
                message: 'Папка не найдена или не в корзине',
            );
        }

        $folder->restoreFromTrash();

        return new RestoreResultDto(
            success: true,
            message: "Папка «{$folder->title}» восстановлена",
            location: 'folder',
        );
    }

    /**
     * Безвозвратно удалить заметку
     */
    public function deleteNote(int $userId, int $noteId): DeleteResultDto
    {
        $note = Note::where('user_id', $userId)->find($noteId);

        if (!$note || !$note->isInTrash()) {
            return new DeleteResultDto(
                success: false,
                message: 'Заметка не найдена или не в корзине',
            );
        }

        $typeLabel = $note->type === Note::TYPE_NOTE ? 'Заметка' : 'Список';
        $title = $note->title;

        // Изображения удаляются автоматически через событие deleting в модели Note
        $note->delete();

        // Обновляем счётчик корзины
        $trash = Trash::where('user_id', $userId)->first();
        if ($trash) {
            $trash->decrementQuantity();
            $trash->save();
        }

        return new DeleteResultDto(
            success: true,
            message: "{$typeLabel} «{$title}» удалена",
        );
    }

    /**
     * Безвозвратно удалить папку
     */
    public function deleteFolder(int $userId, int $folderId): DeleteResultDto
    {
        $folder = Folder::where('user_id', $userId)->find($folderId);

        if (!$folder || !$folder->isInTrash()) {
            return new DeleteResultDto(
                success: false,
                message: 'Папка не найдена или не в корзине',
            );
        }

        // Получаем все заметки в папке (включая те, что в корзине)
        $notes = Note::where('folder_id', $folder->id)
            ->whereNotNull('trash_id')
            ->get();

        $notesCount = $notes->count();

        $title = $folder->title;

        // Сначала удаляем все заметки, принадлежащие папке
        // Изображения удаляются автоматически через событие deleting в модели Note
        foreach ($notes as $note) {
            $note->delete(); // Используем delete() для каждой заметки, чтобы сработал обработчик deleting
        }

        // Затем удаляем саму папку
        $folder->delete();

        // Обновляем счётчик корзины
        $trash = Trash::where('user_id', $userId)->first();
        if ($trash) {
            $trash->decrementQuantity(1 + $notesCount);
            $trash->save();
        }

        return new DeleteResultDto(
            success: true,
            message: "Папка «{$title}» удалена",
        );
    }

    /**
     * Очистить корзину
     */
    public function emptyTrash(int $userId): DeleteResultDto
    {
        // Сначала получаем все папки в корзине
        $folders = Folder::where('user_id', $userId)
            ->whereNotNull('trash_id')
            ->get();

        // Для каждой папки сначала удаляем все заметки, а затем саму папку
        foreach ($folders as $folder) {
            // Получаем все заметки в папке (включая те, что в корзине)
            $notes = Note::where('folder_id', $folder->id)
                ->whereNotNull('trash_id')
                ->get();

            // Удаляем каждую заметку отдельно, чтобы сработал обработчик deleting
            foreach ($notes as $note) {
                $note->delete();
            }

            // Затем удаляем саму папку
            $folder->delete();
        }

        // Затем удаляем заметки без папки
        $orphanNotes = Note::where('user_id', $userId)
            ->whereNotNull('trash_id')
            ->whereNull('folder_id')
            ->get();

        // Удаляем каждую заметку отдельно, чтобы сработал обработчик deleting
        foreach ($orphanNotes as $note) {
            $note->delete();
        }

        // Сбрасываем счётчик корзины
        $trash = Trash::where('user_id', $userId)->first();
        if ($trash) {
            $trash->resetQuantity();
            $trash->save();
        }

        return new DeleteResultDto(
            success: true,
            message: 'Корзина очищена',
        );
    }

    /**
     * Восстановить все элементы из корзины
     */
    public function restoreAll(int $userId): RestoreResultDto
    {
        $user = \App\Models\User::find($userId);
        if (!$user) {
            return new RestoreResultDto(
                success: false,
                message: 'Пользователь не найден',
            );
        }

        $archive = $user->archive;
        $trash = $user->trash;

        // Восстанавливаем все папки
        $folders = Folder::where('user_id', $userId)
            ->whereNotNull('trash_id')
            ->get();

        foreach ($folders as $folder) {
            $folder->restoreFromTrash();
        }

        // Восстанавливаем заметки (без папки) в архив
        $orphanNotes = Note::where('user_id', $userId)
            ->whereNotNull('trash_id')
            ->whereNull('folder_id')
            ->get();

        foreach ($orphanNotes as $note) {
            $note->update([
                'trash_id' => null,
                'archive_id' => $archive->id,
                'moved_to_trash_at' => null,
            ]);
        }

        // Сбрасываем счётчик корзины
        if ($trash) {
            $trash->resetQuantity();
            $trash->save();
        }

        return new RestoreResultDto(
            success: true,
            message: 'Данные восстановлены',
            location: 'archive',
        );
    }

    /**
     * Получить общее количество элементов в корзине
     */
    public function getTotalCount(int $userId): int
    {
        $notesCount = Note::where('user_id', $userId)
            ->whereNotNull('trash_id')
            ->whereNull('folder_id')
            ->count();

        $foldersCount = Folder::where('user_id', $userId)
            ->whereNotNull('trash_id')
            ->count();

        return $notesCount + $foldersCount;
    }

    /**
     * Проверить, активна ли корзина (есть ли в ней элементы)
     */
    public function isActive(int $userId): bool
    {
        return $this->getTotalCount($userId) > 0;
    }

    /**
     * Получить описание для восстановления
     */
    public function getRestoreDescription(int $userId, int $id, string $type): string
    {
        if ($type === 'folder') {
            return 'Папка будет восстановлена';
        }

        $note = Note::where('user_id', $userId)->find($id);
        if (!$note) {
            return '';
        }

        $isChecklist = $note->type === Note::TYPE_CHECKLIST;
        return $isChecklist
            ? 'Список будет перемещен в архив'
            : 'Заметка будет перемещена в архив';
    }

    /**
     * Получить карту сортировок для корзины.
     */
    public function getSortMap(): array
    {
        return [
            'deleted' => 'moved_to_trash_at',
            'title' => 'title',
        ];
    }

    /**
     * Получить направления сортировок для корзины.
     */
    public function getSortDirections(): array
    {
        return [
            'deleted' => 'desc',
            'title' => 'asc',
        ];
    }

    /**
     * Получить удалённые папки с поиском и сортировкой.
     */
    public function getTrashedFolders(
        int $userId,
        ?string $search = null,
        string $sort = 'deleted',
    ): Collection {
        $sortMap = $this->getSortMap();
        $sortDirections = $this->getSortDirections();

        $query = Folder::where('user_id', $userId)
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