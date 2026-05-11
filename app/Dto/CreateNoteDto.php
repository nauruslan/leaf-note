<?php

namespace App\Dto;

/**
 * DTO для создания заметки
 */
readonly class CreateNoteDto
{
    public function __construct(
        public int $userId,
        public string $title,
        public string $content,
        public bool $isFavorite,
        public \App\Dto\LocationDto $location,
    ) {}
}