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
     * Секции создания (сохраняют предыдущую секцию для возврата назад)
     */
    private const CREATE_SECTIONS = ['create-note', 'create-checklist', 'create-folder'];

    /**
     * Определить активную секцию для подсветки
     */
    public function getActiveSection(string $currentSection, ?string $previousSection): string
    {
        // Для секций редактирования подсвечиваем предыдущую секцию
        if (in_array($currentSection, self::EDITING_SECTIONS) && $previousSection) {
            return $previousSection;
        }

        // Для секций создания подсвечиваем саму секцию создания
        return $currentSection;
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
            previousSection: $previousSection,
            previousFolderId: $previousFolderId,
        );
    }
}
