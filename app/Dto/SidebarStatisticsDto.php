<?php

namespace App\Dto;

/**
 * DTO для статистики сайдбара
 */
readonly class SidebarStatisticsDto
{
    public function __construct(
        public int $dashboard,
        public int $safe,
        public int $archive,
        public int $favorite,
    ) {}
}