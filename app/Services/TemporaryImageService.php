<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

/**
 * Сервис для управления временными изображениями
 *
 * Решает проблему "потерянных" изображений, которые загружаются
 * при создании/редактировании заметки, но остаются в storage
 * если заметка не была сохранена.
 */
class TemporaryImageService
{
    private const SESSION_KEY = 'temporary_images';
    private const PENDING_DELETE_KEY = 'pending_delete_images';
    private const STORAGE_PATH = 'notes/images';
    private const BACKUP_PATH = 'notes/.backup';

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
    public function deleteUnsavedImages(): void
    {
        $images = $this->getAll();

        foreach ($images as $item) {
            // Проверяем, используется ли изображение в какой-либо заметке
            if (!$this->isFileUsedInNotes($item['path'])) {
                $this->deleteFile($item['path']);
            }
        }

        $this->clear();
    }

    /**
     * Добавить изображение в список на удаление (мягкое удаление)
     * Изображение не удаляется физически, а помечается для удаления
     * Перед пометкой создается бэкап файла для возможности восстановления
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

        // Создаем бэкап файла перед удалением
        $this->createBackup($path);

        $pendingDelete[] = [
            'path' => $path,
            'marked_at' => time(),
        ];
        Session::put(self::PENDING_DELETE_KEY, $pendingDelete);
    }

    /**
     * Восстановить изображение из списка на удаление (при undo)
     * Возвращает true если файл был успешно восстановлен из бэкапа
     */
    public function restoreFromDeletion(string $path): bool
    {
        $pendingDelete = $this->getPendingDelete();
        $pendingDelete = array_filter($pendingDelete, fn($item) => $item['path'] !== $path);
        Session::put(self::PENDING_DELETE_KEY, array_values($pendingDelete));

        // Восстанавливаем файл из бэкапа
        return $this->restoreFromBackup($path);
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
     */
    public function executePendingDeletion(): void
    {
        $pendingDelete = $this->getPendingDelete();

        foreach ($pendingDelete as $item) {
            // Проверяем, используется ли изображение в какой-либо заметке
            if (!$this->isFileUsedInNotes($item['path'])) {
                $this->deleteFile($item['path']);
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
     * Создать бэкап файла изображения
     * Бэкап хранится в отдельной директории для возможности восстановления
     */
    private function createBackup(string $path): bool
    {
        try {
            $cleanPath = str_replace('..', '', $path);

            if (!str_starts_with($cleanPath, 'notes/')) {
                return false;
            }

            // Проверяем существование оригинального файла
            if (!Storage::disk('public')->exists($cleanPath)) {
                return false;
            }

            // Создаем директорию для бэкапов если её нет
            if (!Storage::disk('public')->exists(self::BACKUP_PATH)) {
                Storage::disk('public')->makeDirectory(self::BACKUP_PATH);
            }

            // Генерируем уникальное имя для бэкапа на основе оригинального пути
            $backupName = md5($cleanPath) . '_' . basename($cleanPath);
            $backupPath = self::BACKUP_PATH . '/' . $backupName;

            // Копируем файл в бэкап
            $content = Storage::disk('public')->get($cleanPath);
            return Storage::disk('public')->put($backupPath, $content);

        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

    /**
     * Восстановить файл изображения из бэкапа
     */
    private function restoreFromBackup(string $path): bool
    {
        try {
            $cleanPath = str_replace('..', '', $path);

            if (!str_starts_with($cleanPath, 'notes/')) {
                return false;
            }

            // Генерируем имя бэкапа
            $backupName = md5($cleanPath) . '_' . basename($cleanPath);
            $backupPath = self::BACKUP_PATH . '/' . $backupName;

            // Проверяем существование бэкапа
            if (!Storage::disk('public')->exists($backupPath)) {
                return false;
            }

            // Восстанавливаем файл из бэкапа
            $content = Storage::disk('public')->get($backupPath);
            $restored = Storage::disk('public')->put($cleanPath, $content);

            // Удаляем бэкап после восстановления
            if ($restored) {
                Storage::disk('public')->delete($backupPath);
            }

            return $restored;

        } catch (\Exception $e) {
            report($e);
            return false;
        }
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
                if (!$this->isFileUsedInNotes($file)) {
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
     */
    private function isFileUsedInNotes(string $path): bool
    {
        return \App\Models\Note::where('content', 'LIKE', '%' . $path . '%')->exists();
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