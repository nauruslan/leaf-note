<?php

namespace App\Dto;

/**
 * DTO для обновления заметки
 */
readonly class UpdateNoteDto
{
    public function __construct(
        public int $userId,
        public int $noteId,
        public string $title,
        public string $content,
        public bool $isFavorite,
        public \App\Dto\LocationDto $location,
    ) {}
}