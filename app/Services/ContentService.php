<?php

namespace App\Services;

/**
 * Сервис для работы с контентом заметок
 */
class ContentService
{
    private const EMPTY_CHECKLIST_STRUCTURE = '{"type":"doc","content":[{"type":"checklist","content":[]}]}';
    private const EMPTY_NOTE_STRUCTURE = '{"type":"doc","content":[{"type":"note","content":[]}]}';

    /**
     * Нормализовать контент чеклиста
     */
    public function normalizeChecklistContent(mixed $content): string
    {
        return $this->normalizeContent($content, self::EMPTY_CHECKLIST_STRUCTURE);
    }

    /**
     * Нормализовать контент заметки
     */
    public function normalizeNoteContent(mixed $content): string
    {
        return $this->normalizeContent($content, self::EMPTY_NOTE_STRUCTURE);
    }

    /**
     * Нормализовать контент с заданной структурой по умолчанию
     */
    private function normalizeContent(mixed $content, string $defaultStructure): string
    {
        if (is_string($content) && $content === '') {
            return $defaultStructure;
        }

        if (!is_string($content)) {
            if (is_array($content) || is_object($content)) {
                $content = json_encode($content, JSON_UNESCAPED_UNICODE);
            } else {
                return $defaultStructure;
            }
        }

        try {
            $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

            if (!is_array($decoded) || empty($decoded)) {
                return $defaultStructure;
            }

            return json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\JsonException) {
            return $defaultStructure;
        }
    }

    /**
     * Извлечь текст из контента
     */
    public function extractTextFromContent(mixed $content): string
    {
        if (is_string($content)) {
            try {
                $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                return '';
            }
        } else {
            $data = $content;
        }

        if (!is_array($data) || !isset($data['content'])) {
            return '';
        }

        return $this->collectTextFromContent($data['content']);
    }

    /**
     * Собрать текст из контента
     */
    private function collectTextFromContent(array $content): string
    {
        $blocks = [];

        foreach ($content as $node) {
            if (!is_array($node)) {
                continue;
            }

            $blockText = $this->extractTextFromNode($node);
            if ($blockText !== '') {
                $blocks[] = $blockText;
            }
        }

        return implode("\n", $blocks);
    }

    /**
     * Извлечь текст из узла
     */
    private function extractTextFromNode(array $node): string
    {
        $texts = [];

        if (isset($node['type']) && $node['type'] === 'text' && isset($node['text'])) {
            $texts[] = $node['text'];
        }

        if (isset($node['content']) && is_array($node['content'])) {
            $nestedText = $this->collectInlineTextFromContent($node['content']);
            if ($nestedText !== '') {
                $texts[] = $nestedText;
            }
        }

        return implode(' ', $texts);
    }

    /**
     * Собрать inline текст из контента
     */
    private function collectInlineTextFromContent(array $content): string
    {
        $texts = [];

        foreach ($content as $node) {
            if (!is_array($node)) {
                continue;
            }

            if (isset($node['type']) && $node['type'] === 'text' && isset($node['text'])) {
                $texts[] = $node['text'];
            }

            if (isset($node['content']) && is_array($node['content'])) {
                $texts[] = $this->collectInlineTextFromContent($node['content']);
            }
        }

        return trim(implode(' ', $texts));
    }

    /**
     * Извлечь пути к изображениям из контента
     */
    public function extractImagePaths(mixed $content): array
    {
        if (empty($content)) {
            return [];
        }

        if (is_string($content)) {
            try {
                $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                return [];
            }
        } else {
            $data = $content;
        }

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

        return $paths;
    }

    /**
     * Проверить, является ли контент валидным JSON
     */
    public function isValidJsonContent(string $content): bool
    {
        try {
            json_decode($content, true, 512, JSON_THROW_ON_ERROR);
            return true;
        } catch (\JsonException) {
            return false;
        }
    }

    /**
     * Преобразовать контент в массив для сохранения в базу данных
     */
    public function contentToArray(mixed $content): array
    {
        if (is_array($content)) {
            return $content;
        }

        if (is_string($content)) {
            try {
                return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                return json_decode($this->normalizeNoteContent(''), true, 512, JSON_THROW_ON_ERROR);
            }
        }

        return json_decode($this->normalizeNoteContent(''), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Преобразовать контент в массив для чеклиста
     */
    public function checklistContentToArray(mixed $content): array
    {
        if (is_array($content)) {
            return $content;
        }

        if (is_string($content)) {
            try {
                return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                return json_decode($this->normalizeChecklistContent(''), true, 512, JSON_THROW_ON_ERROR);
            }
        }

        return json_decode($this->normalizeChecklistContent(''), true, 512, JSON_THROW_ON_ERROR);
    }
}
