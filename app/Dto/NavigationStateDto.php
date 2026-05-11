<?php

namespace App\Dto;

/**
 * DTO для состояния навигации сайдбара
 */
readonly class NavigationStateDto
{
    public function __construct(
        public string $section,
        public ?int $folderId,
        public ?string $previousSection,
        public ?int $previousFolderId,
    ) {}
}