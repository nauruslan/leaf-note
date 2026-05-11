<?php

namespace App\Dto;

/**
 * DTO для настроек корзины
 */
readonly class TrashSettingsDto
{
    public function __construct(
        public ?string $autoDeleteDays,
    ) {}
}