<?php

namespace App\Dto;

/**
 * DTO для статистики пользователя
 */
readonly class UserStatisticsDto
{
    public function __construct(
        public int $notesCount,
        public int $checklistsCount,
        public int $foldersCount,
    ) {}
}