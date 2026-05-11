<?php

namespace App\Dto;

/**
 * DTO для данных профиля пользователя
 */
readonly class ProfileDto
{
    public function __construct(
        public string $name,
        public string $email,
        public bool $notificationsEnabled,
    ) {}
}
