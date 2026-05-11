<?php

namespace App\Dto;

/**
 * DTO для результата удаления из корзины
 */
readonly class DeleteResultDto
{
    public function __construct(
        public bool $success,
        public string $message,
    ) {}
}