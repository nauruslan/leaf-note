<?php

namespace App\Dto;

/**
 * DTO для результата восстановления из корзины
 */
readonly class RestoreResultDto
{
    public function __construct(
        public bool $success,
        public string $message,
        public ?string $location = null,
    ) {}
}