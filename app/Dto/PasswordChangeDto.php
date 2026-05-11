<?php

namespace App\Dto;

/**
 * DTO для смены пароля аккаунта
 */
readonly class PasswordChangeDto
{
    public function __construct(
        public string $currentPassword,
        public string $newPassword,
        public string $confirmPassword,
    ) {}
}