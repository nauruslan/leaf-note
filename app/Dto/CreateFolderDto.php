<?php

namespace App\Dto;

/**
 * DTO для создания папки
 */
readonly class CreateFolderDto
{
    public function __construct(
        public int $userId,
        public string $title,
        public string $color,
        public string $icon,
    ) {}
}