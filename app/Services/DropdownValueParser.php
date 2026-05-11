<?php

namespace App\Services;

use App\Dto\LocationDto;

/**
 * Сервис для парсинга dropdown значений местоположения
 */
class DropdownValueParser
{
    private const SAFE_PREFIX = 'safe_';
    private const ARCHIVE_PREFIX = 'archive_';

    /**
     * Парсить dropdown значение в LocationDto
     */
    public function parse(?string $value): LocationDto
    {
        if ($value === null) {
            return new LocationDto();
        }

        if ($this->isSafeValue($value)) {
            return new LocationDto(
                safeId: $this->extractSafeId($value),
            );
        }

        if ($this->isArchiveValue($value)) {
            return new LocationDto(
                archiveId: $this->extractArchiveId($value),
            );
        }

        if ($this->isFolderValue($value)) {
            return new LocationDto(
                folderId: $this->extractFolderId($value),
            );
        }

        return new LocationDto();
    }

    /**
     * Проверить, является ли значение сейфом
     */
    public function isSafeValue(?string $value): bool
    {
        return $value !== null && str_starts_with($value, self::SAFE_PREFIX);
    }

    /**
     * Проверить, является ли значение архивом
     */
    public function isArchiveValue(?string $value): bool
    {
        return $value !== null && str_starts_with($value, self::ARCHIVE_PREFIX);
    }

    /**
     * Проверить, является ли значение папкой
     */
    public function isFolderValue(?string $value): bool
    {
        return $value !== null && is_numeric($value) && !$this->isSafeValue($value) && !$this->isArchiveValue($value);
    }

    /**
     * Извлечь ID сейфа из значения
     */
    public function extractSafeId(?string $value): ?int
    {
        if (!$this->isSafeValue($value)) {
            return null;
        }

        $id = substr($value, strlen(self::SAFE_PREFIX));
        return is_numeric($id) ? (int) $id : null;
    }

    /**
     * Извлечь ID архива из значения
     */
    public function extractArchiveId(?string $value): ?int
    {
        if (!$this->isArchiveValue($value)) {
            return null;
        }

        $id = substr($value, strlen(self::ARCHIVE_PREFIX));
        return is_numeric($id) ? (int) $id : null;
    }

    /**
     * Извлечь ID папки из значения
     */
    public function extractFolderId(?string $value): ?int
    {
        if (!$this->isFolderValue($value)) {
            return null;
        }

        return (int) $value;
    }
}