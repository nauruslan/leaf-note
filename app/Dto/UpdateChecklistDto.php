<?php

namespace App\Dto;

/**
 * DTO для обновления чеклиста
 */
readonly class UpdateChecklistDto
{
    public function __construct(
        public int $userId,
        public int $noteId,
        public string $title,
        public array $content,
        public bool $isFavorite,
        public LocationDto $location,
    ) {}
}