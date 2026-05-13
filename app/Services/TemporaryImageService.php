<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

/**
 * Сервис для управления временными изображениями
 */
class TemporaryImageService
{
    private const SESSION_KEY = 'temporary_images';
    private const PENDING_DELETE_KEY = 'pending_delete_images';
    private const STORAGE_PATH = 'notes/images';

    /**
     * Добавить путь к временному изображению
     */
    public function add(string $path): void
    {
        $images = $this->getAll();

        // Проверяем, есть ли уже такой путь в списке
        foreach ($images as $item) {
            if (isset($item['path']) && $item['path'] === $path) {
                return; // Уже существует
            }
        }

        $images[] = [
            'path' => $path,
            'created_at' => time(),
        ];
        Session::put(self::SESSION_KEY, $images);
    }

    /**
     * Удалить путь из списка временных изображений
     */
    public function remove(string $path): void
    {
        $images = $this->getAll();
        $images = array_filter($images, fn($item) => $item['path'] !== $path);
        Session::put(self::SESSION_KEY, array_values($images));
    }

    /**
     * Получить все временные изображения
     */
    public function getAll(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }

    /**
     * Очистить список временных изображений (без удаления файлов)
     */
    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    /**
     * Удалить все временные изображения из хранилища и очистить список
     */
    public function deleteAll(): void
    {
        $images = $this->getAll();

        foreach ($images as $item) {
            $this->deleteFile($item['path']);
        }

        $this->clear();
    }

    /**
     * Удалить временные изображения, созданные в текущей сессии создания заметки
     * Вызывается при уходе со страницы создания заметки без сохранения
     * Удаляет только те изображения, которые не используются в других заметках
     */
    public function deleteUnsavedImages(?int $excludeNoteId = null): void
    {
        $images = $this->getAll();

        foreach ($images as $item) {
            // Проверяем, используется ли изображение в какой-либо заметке (исключая текущую)
            if (!$this->isFileUsedInNotes($item['path'], $excludeNoteId)) {
                $this->deleteFile($item['path']);
            }
        }

        $this->clear();
    }

    /**
     * Добавить изображение в список на удаление (мягкое удаление)
     * Изображение не удаляется физически, а помечается для удаления
     */
    public function markForDeletion(string $path): void
    {
        $pendingDelete = $this->getPendingDelete();

        // Проверяем, есть ли уже такой путь в списке
        foreach ($pendingDelete as $item) {
            if (isset($item['path']) && $item['path'] === $path) {
                return; // Уже в списке
            }
        }

        $pendingDelete[] = [
            'path' => $path,
            'marked_at' => time(),
        ];
        Session::put(self::PENDING_DELETE_KEY, $pendingDelete);
    }

    /**
     * Восстановить изображение из списка на удаление (при undo)
     */
    public function restoreFromDeletion(string $path): bool
    {
        $pendingDelete = $this->getPendingDelete();
        $pendingDelete = array_filter($pendingDelete, fn($item) => $item['path'] !== $path);
        Session::put(self::PENDING_DELETE_KEY, array_values($pendingDelete));

        return true;
    }

    /**
     * Получить все изображения, помеченные на удаление
     */
    public function getPendingDelete(): array
    {
        return Session::get(self::PENDING_DELETE_KEY, []);
    }

    /**
     * Выполнить фактическое удаление всех помеченных изображений
     *
     * @param array $excludePaths Пути изображений, которые НЕ нужно удалять
     */
    public function executePendingDeletion(array $excludePaths = [], ?int $excludeNoteId = null): void
    {
        $pendingDelete = $this->getPendingDelete();

        foreach ($pendingDelete as $item) {
            $path = $item['path'];

            // Проверяем, находится ли путь в списке исключений
            if (in_array($path, $excludePaths)) {
                continue;
            }

            // Проверяем, используется ли изображение в какой-либо заметке (исключая текущую)
            if (!$this->isFileUsedInNotes($path, $excludeNoteId)) {
                $this->deleteFile($path);
            }
        }

        $this->clearPendingDeletion();
    }

    /**
     * Очистить список изображений на удаление (без удаления файлов)
     */
    public function clearPendingDeletion(): void
    {
        Session::forget(self::PENDING_DELETE_KEY);
    }

    /**
     * Восстановить все изображения из списка на удаление
     * Используется при отмене редактирования (cancel)
     */
    public function restoreAllPendingDeletions(): void
    {
        $pendingDelete = $this->getPendingDelete();

        foreach ($pendingDelete as $item) {
            $this->restoreFromDeletion($item['path']);
        }

        $this->clearPendingDeletion();
    }

    /**
     * Очистить изображения, помеченных на удаление
     * Используется при уходе со страницы редактирования/создания заметки
     * Выполняет фактическое удаление файлов, если они не используются в других заметках
     */
    public function cleanupPendingBackups(): void
    {
        $pendingDelete = $this->getPendingDelete();

        foreach ($pendingDelete as $item) {
            $path = $item['path'];

            // Проверяем, используется ли изображение в какой-либо заметке
            if (!$this->isFileUsedInNotes($path, null)) {
                // Удаляем сам файл изображения
                $this->deleteFile($path);
            }
        }

        // Очищаем список помеченных на удаление
        $this->clearPendingDeletion();
    }

    /**
     * Проверить, помечено ли изображение на удаление
     */
    public function isMarkedForDeletion(string $path): bool
    {
        $pendingDelete = $this->getPendingDelete();
        foreach ($pendingDelete as $item) {
            if (isset($item['path']) && $item['path'] === $path) {
                return true;
            }
        }
        return false;
    }

    /**
     * Удалить файл изображения из хранилища
     */
    private function deleteFile(string $path): bool
    {
        try {
            $cleanPath = str_replace('..', '', $path);

            if (str_starts_with($cleanPath, 'notes/') &&
                Storage::disk('public')->exists($cleanPath)) {
                return Storage::disk('public')->delete($cleanPath);
            }
        } catch (\Exception $e) {
            report($e);
        }

        return false;
    }


    /**
     * Удалить старые временные изображения (garbage collection)
     * Удаляет изображения старше указанного количества часов
     * Поддерживает структуру подпапок с датами (images/19042026/)
     */
    public function garbageCollect(int $olderThanHours = 24): int
    {
        $deletedCount = 0;
        $threshold = time() - ($olderThanHours * 3600);

        // Получаем все файлы рекурсивно (включая подпапки с датами)
        $files = Storage::disk('public')->allFiles(self::STORAGE_PATH);

        foreach ($files as $file) {
            $lastModified = Storage::disk('public')->lastModified($file);

            if ($lastModified < $threshold) {
                // Проверяем, что файл не используется ни в одной заметке
                if (!$this->isFileUsedInNotes($file, null)) {
                    Storage::disk('public')->delete($file);
                    $deletedCount++;
                }
            }
        }

        // Удаляем пустые папки с датами
        $this->cleanupEmptyDateFolders();

        return $deletedCount;
    }

    /**
     * Удалить пустые папки с датами
     */
    private function cleanupEmptyDateFolders(): void
    {
        $directories = Storage::disk('public')->directories(self::STORAGE_PATH);

        foreach ($directories as $directory) {
            $files = Storage::disk('public')->files($directory);
            if (empty($files)) {
                Storage::disk('public')->deleteDirectory($directory);
            }
        }
    }

    /**
     * Проверить, используется ли файл в какой-либо заметке
     *
     * @param string $path Путь к файлу
     * @param int|null $excludeNoteId ID заметки, которую нужно исключить из проверки
     * @return bool
     */
    private function isFileUsedInNotes(string $path, ?int $excludeNoteId = null): bool
    {
        $query = \App\Models\Note::where('content', 'LIKE', '%' . $path . '%');

        // Исключаем текущую заметку из проверки (при редактировании)
        if ($excludeNoteId !== null) {
            $query->where('id', '!=', $excludeNoteId);
        }

        return $query->exists();
    }

    /**
     * Удалить изображения, которые были в старом контенте, но отсутствуют в новом
     *
     * @param array $oldPaths Пути изображений из старого контента
     * @param array $newPaths Пути изображений из нового контента
     * @param int|null $excludeNoteId ID заметки, которую нужно исключить из проверки
     */
    public function deleteRemovedImages(array $oldPaths, array $newPaths, ?int $excludeNoteId = null): void
    {
        $removedPaths = array_diff($oldPaths, $newPaths);

        foreach ($removedPaths as $path) {
            // Проверяем, используется ли изображение в других заметках (исключая текущую)
            if (!$this->isFileUsedInNotes($path, $excludeNoteId)) {
                // Помечаем изображение на удаление
                $this->markForDeletion($path);
            }
        }
    }

    /**
     * Удалить все изображения пользователя (при удалении пользователя)
     */
    public function deleteUserImages(int $userId): int
    {
        $deletedCount = 0;
        $notes = \App\Models\Note::where('user_id', $userId)->get();

        foreach ($notes as $note) {
            $paths = $note->getImagePaths();
            foreach ($paths as $path) {
                if ($this->deleteFile($path)) {
                    $deletedCount++;
                }
            }
        }

        return $deletedCount;
    }
}