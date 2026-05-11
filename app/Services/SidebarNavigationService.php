<?php

namespace App\Services;

use App\Dto\NavigationStateDto;

/**
 * Сервис для логики навигации сайдбара
 */
class SidebarNavigationService
{
    /**
     * Секции редактирования (для подсветки предыдущей секции)
     */
    private const EDITING_SECTIONS = ['edit-note', 'edit-checklist', 'edit-folder'];

    /**
     * Секции создания (не сохраняют предыдущую секцию)
     */
    private const CREATE_SECTIONS = ['create-note', 'create-checklist', 'create-folder'];

    /**
     * Определить активную секцию для подсветки
     */
    public function getActiveSection(string $currentSection, ?string $previousSection): string
    {
        if (in_array($currentSection, self::EDITING_SECTIONS) && $previousSection) {
            return $previousSection;
        }

        return $currentSection;
    }

    /**
     * Проверить, нужно ли сохранять предыдущую секцию
     */
    public function shouldSavePreviousSection(string $currentSection): bool
    {
        return !in_array($currentSection, self::CREATE_SECTIONS);
    }

    /**
     * Подготовить состояние навигации
     */
    public function prepareNavigationState(
        string $section,
        ?int $folderId,
        string $previousSection,
        ?int $previousFolderId,
    ): NavigationStateDto {
        return new NavigationStateDto(
            section: $section,
            folderId: $folderId,
            previousSection: $this->shouldSavePreviousSection($section) ? $previousSection : null,
            previousFolderId: $this->shouldSavePreviousSection($section) ? $previousFolderId : null,
        );
    }
}
