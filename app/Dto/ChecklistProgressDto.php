<?php

namespace App\Dto;

/**
 * DTO для хранения информации о прогрессе чеклиста
 */
readonly class ChecklistProgressDto
{
    public function __construct(
        public int $completed,
        public int $total,
        public int $percentage,
        public string $color,
    ) {}

    /**
     * Проверить, пуст ли чеклист
     */
    public function isEmpty(): bool
    {
        return $this->total === 0;
    }

    /**
     * Проверить, выполнен ли чеклист полностью
     */
    public function isComplete(): bool
    {
        return $this->total > 0 && $this->completed === $this->total;
    }
}