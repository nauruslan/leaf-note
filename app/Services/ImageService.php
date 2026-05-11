<?php

namespace App\Services;

use App\Models\Note;
use Illuminate\Support\Facades\Storage;

/**
 * Сервис для работы с изображениями в контенте заметок
 */
class ImageService
{
    /**
     * Извлечь пути к изображениям из контента
     */
    public function extractImagePaths(mixed $content): array
    {
        if (empty($content)) {
            return [];
        }

        $data = is_string($content)
            ? json_decode($content, true)
            : $content;

        if (!is_array($data) || !isset($data['content'])) {
            return [];
        }

        return $this->extractImagePathsFromNodes($data['content']);
    }

    /**
     * Рекурсивно извлечь пути изображений из узлов
     */
    private function extractImagePathsFromNodes(array $content): array
    {
        $paths = [];

        foreach ($content as $node) {
            if (!is_array($node)) {
                continue;
            }

            if (isset($node['type']) && $node['type'] === 'image') {
                if (isset($node['attrs']['path'])) {
                    $paths[] = $node['attrs']['path'];
                } elseif (isset($node['attrs']['src'])) {
                    $src = $node['attrs']['src'];
                    if (str_starts_with($src, '/storage/')) {
                        $paths[] = substr($src, strlen('/storage/'));
                    } elseif (str_starts_with($src, 'notes/')) {
                        $paths[] = $src;
                    }
                }
            }

            if (isset($node['content']) && is_array($node['content'])) {
                $paths = array_merge($paths, $this->extractImagePathsFromNodes($node['content']));
            }
        }

        return array_values(array_unique($paths));
    }

    /**
     * Удалить изображения из хранилища
     */
    public function deleteImagesFromStorage(array $paths): void
    {
        foreach ($paths as $path) {
            try {
                $cleanPath = str_replace('..', '', $path);

                if (str_starts_with($cleanPath, 'notes/') &&
                    Storage::disk('public')->exists($cleanPath)) {
                    Storage::disk('public')->delete($cleanPath);
                }
            } catch (\Exception $e) {
                report($e);
            }
        }
    }

    /**
     * Удалить все изображения заметки
     */
    public function deleteNoteImages(Note $note): void
    {
        $paths = $this->extractImagePaths($note->content);
        $this->deleteImagesFromStorage($paths);
    }

    /**
     * Сравнить два набора путей изображений и вернуть разницу
     *
     * @return array Массив с ключами 'added' и 'removed'
     */
    public function compareImagePaths(array $originalPaths, array $currentPaths): array
    {
        return [
            'added' => array_values(array_diff($currentPaths, $originalPaths)),
            'removed' => array_values(array_diff($originalPaths, $currentPaths)),
        ];
    }
}