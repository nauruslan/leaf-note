<?php

namespace App\Dto;

/**
 * DTO для хранения информации о местоположении заметки
 */
readonly class LocationDto
{
    public function __construct(
        public ?int $folderId = null,
        public ?int $safeId = null,
        public ?int $archiveId = null,
    ) {}

    /**
     * Проверить, есть ли выбранное местоположение
     */
    public function hasLocation(): bool
    {
        return $this->folderId !== null || $this->safeId !== null || $this->archiveId !== null;
    }

    /**
     * Получить тип местоположения
     */
    public function getType(): ?string
    {
        if ($this->folderId !== null) {
            return 'folder';
        }
        if ($this->safeId !== null) {
            return 'safe';
        }
        if ($this->archiveId !== null) {
            return 'archive';
        }
        return null;
    }

    /**
     * Получить ID местоположения
     */
    public function getId(): ?int
    {
        return $this->folderId ?? $this->safeId ?? $this->archiveId;
    }
}