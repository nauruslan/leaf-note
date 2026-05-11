<?php

namespace App\Dto;

/**
 * DTO для обновления папки
 */
readonly class UpdateFolderDto
{
    public function __construct(
        public int $userId,
        public int $folderId,
        public string $title,
        public string $color,
        public string $icon,
    ) {}
}