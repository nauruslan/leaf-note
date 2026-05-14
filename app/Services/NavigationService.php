<?php

namespace App\Services;

use App\Dto\NavigationStateDto;
use App\Services\StateManager;
use App\Services\TemporaryImageService;
use Illuminate\Support\Facades\Auth;

/**
 * Сервис для управления навигацией в приложении
 */
class NavigationService
{
    /**
     * Секции создания
     */
    private const CREATE_SECTIONS = ['create-note', 'create-checklist', 'create-folder'];

    /**
     * Секции редактирования (для подсветки предыдущей секции)
     */
    private const EDITING_SECTIONS = ['edit-note', 'edit-checklist', 'edit-folder'];

    /**
     * Секции контекста сейфа
     */
    private const SAFE_CONTEXT_SECTIONS = ['safe-section', 'edit-note', 'edit-checklist', 'create-note', 'create-checklist'];

    /**
     * Выполнить навигацию к указанной секции
     */
    public function navigateTo(
        string $section,
        ?int $folderId = null,
        ?int $noteId = null,
        ?string $currentSection = null,
        ?int $currentFolderId = null,
        ?int $currentNoteId = null
    ): void {
        // Сохраняем текущую секцию как предыдущую перед переходом
        $this->savePreviousSection($section, $currentSection, $currentFolderId, $currentNoteId);

        // Обрабатываем выход из контекста сейфа
        $this->handleSafeContextExit($section, $currentSection);

        // Очищаем временные изображения при необходимости
        $this->handleImageCleanup($section, $currentSection);

        // Обновляем состояние
        $this->updateState($section, $folderId, $noteId);
    }

    /**
     * Проверить, выходим ли из контекста сейфа
     */
    public function isLeavingSafeContext(string $fromSection, string $toSection): bool
    {
        return in_array($fromSection, self::SAFE_CONTEXT_SECTIONS) &&
               !in_array($toSection, self::SAFE_CONTEXT_SECTIONS);
    }

    /**
     * Проверить, нужно ли очищать изображения
     */
    public function shouldCleanupImages(string $fromSection, string $toSection): bool
    {
        return ($fromSection === 'create-note' || $fromSection === 'edit-note') &&
               $toSection !== 'create-note' && $toSection !== 'edit-note';
    }

    /**
     * Определить активную секцию для подсветки в сайдбаре
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
     * Подготовить состояние навигации для сайдбара
     */
    public function prepareNavigationState(
        string $section,
        ?int $folderId,
        string $previousSection,
        ?int $previousFolderId,
        ?int $previousNoteId = null,
    ): NavigationStateDto {
        return new NavigationStateDto(
            section: $section,
            folderId: $folderId,
            previousSection: $previousSection,
            previousFolderId: $previousFolderId,
            previousNoteId: $previousNoteId,
        );
    }

    /**
     * Сохранить предыдущую секцию
     */
    private function savePreviousSection(
        string $newSection,
        ?string $currentSection,
        ?int $currentFolderId,
        ?int $currentNoteId
    ): void {

        // Получаем текущее сохраненное значение previous_section
        $savedPreviousSection = StateManager::get('previous_section');

        // Если переходим на страницу создания, всегда сохраняем текущую секцию как предыдущую
        if (in_array($newSection, self::CREATE_SECTIONS)) {
            StateManager::set('previous_section', $currentSection);
            StateManager::set('previous_folderId', $currentFolderId);
            StateManager::set('previous_noteId', $currentNoteId);
        }
        // В остальных случаях сохраняем предыдущую секцию только если она не совпадает с новой
        elseif ($currentSection && $currentSection !== $newSection) {
            StateManager::set('previous_section', $currentSection);
            StateManager::set('previous_folderId', $currentFolderId);
            StateManager::set('previous_noteId', $currentNoteId);
        }
    }

    /**
     * Обработать выход из контекста сейфа
     */
    private function handleSafeContextExit(string $newSection, ?string $currentSection): void
    {
        if ($this->isLeavingSafeContext($currentSection, $newSection)) {
            StateManager::remove('safe_unlocked');
        }
    }

    /**
     * Обработать очистку изображений
     */
    private function handleImageCleanup(string $newSection, ?string $currentSection): void
    {
        if ($this->shouldCleanupImages($currentSection, $newSection)) {
            $temporaryImageService = app(TemporaryImageService::class);
            $temporaryImageService->cleanupPendingBackups();
        }
    }

    /**
     * Обновить состояние навигации
     */
    private function updateState(string $section, ?int $folderId, ?int $noteId): void
    {
        StateManager::set('section', $section);
        StateManager::set('folderId', $folderId);
        StateManager::set('noteId', $noteId);
    }
}