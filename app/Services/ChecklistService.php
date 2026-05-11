<?php

namespace App\Services;

use App\Dto\ChecklistProgressDto;
use App\Models\Note;

/**
 * Сервис для работы с чеклистами
 */
class ChecklistService
{
    /**
     * Получить прогресс чеклиста
     */
    public function getProgress(Note $checklist): ChecklistProgressDto
    {
        if (!$checklist->isChecklist() || empty($checklist->content)) {
            return new ChecklistProgressDto(
                completed: 0,
                total: 0,
                percentage: 0,
                color: '#FF4C4C',
            );
        }

        $data = is_string($checklist->content)
            ? json_decode($checklist->content, true)
            : $checklist->content;

        if (!is_array($data) || !isset($data['content'])) {
            return new ChecklistProgressDto(
                completed: 0,
                total: 0,
                percentage: 0,
                color: '#FF4C4C',
            );
        }

        $stats = $this->countItems($data['content']);

        $percentage = $stats['total'] > 0
            ? (int) round(($stats['completed'] / $stats['total']) * 100)
            : 0;

        $color = $this->getProgressColor($percentage);

        return new ChecklistProgressDto(
            completed: $stats['completed'],
            total: $stats['total'],
            percentage: $percentage,
            color: $color,
        );
    }

    /**
     * Подсчитать количество элементов в чеклисте
     */
    public function countItems(array $content): array
    {
        $completed = 0;
        $total = 0;

        foreach ($content as $node) {
            if (!is_array($node)) {
                continue;
            }

            if (isset($node['type']) && $node['type'] === 'checklistItem') {
                $total++;
                if (isset($node['attrs']['checked']) && $node['attrs']['checked'] === true) {
                    $completed++;
                }
            }

            if (isset($node['content']) && is_array($node['content'])) {
                $nested = $this->countItems($node['content']);
                $completed += $nested['completed'];
                $total += $nested['total'];
            }
        }

        return ['completed' => $completed, 'total' => $total];
    }

    /**
     * Получить цвет прогресса по проценту
     */
    public function getProgressColor(int $percentage): string
    {
        return match (true) {
            $percentage <= 10 => '#FF4C4C',
            $percentage <= 30 => '#FF8A4C',
            $percentage <= 50 => '#FFC04C',
            $percentage <= 70 => '#B4D84C',
            $percentage <= 90 => '#6ED84C',
            default => '#2ABF2A',
        };
    }

    /**
     * Проверить, пуст ли чеклист
     */
    public function isEmpty(Note $checklist): bool
    {
        if (!$checklist->isChecklist() || empty($checklist->content)) {
            return true;
        }

        $data = is_string($checklist->content)
            ? json_decode($checklist->content, true)
            : $checklist->content;

        if (!is_array($data) || !isset($data['content'])) {
            return true;
        }

        $stats = $this->countItems($data['content']);
        return $stats['total'] === 0;
    }
}