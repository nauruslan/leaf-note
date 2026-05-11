<?php

namespace App\Dto;

/**
 * DTO для пароля сейфа
 */
readonly class SafePasswordDto
{
    public function __construct(
        public ?string $currentPassword = null,
        public string $password = '',
        public string $confirmPassword = '',
    ) {}
}