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
    private const STORAGE_PATH = 'notes/images';

    /**
     * Добавить путь к временному изображению
     */
    public function add(string $path): void
    {
        $images = $this->getAll();

        if (!in_array($path, $images)) {
            $images[] = [
                'path' => $path,
                'created_at' => time(),
            ];
            Session::put(self::SESSION_KEY, $images);
        }
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
     */
    public function deleteUnsavedImages(): void
    {
        $this->deleteAll();
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
     */
    public function garbageCollect(int $olderThanHours = 24): int
    {
        $deletedCount = 0;
        $threshold = time() - ($olderThanHours * 3600);

        // Получаем все файлы в директории временных изображений
        $files = Storage::disk('public')->files(self::STORAGE_PATH);

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

        return $deletedCount;
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
