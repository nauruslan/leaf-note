<?php

namespace App\Dto;

/**
 * DTO для создания чеклиста
 */
readonly class CreateChecklistDto
{
    public function __construct(
        public int $userId,
        public string $title,
        public array $content,
        public bool $isFavorite,
        public LocationDto $location,
    ) {}
}