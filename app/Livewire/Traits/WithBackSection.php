<?php

namespace App\Livewire\Traits;

use App\Services\StateManager;

/**
 * Трейт для навигации назад к предыдущей секции
 */
trait WithBackSection
{
    /**
     * Навигация назад к предыдущей секции
     */
    public function back(): void
    {
        $previousSection = StateManager::get('previous_section');
        $previousFolderId = StateManager::get('previous_folderId');
        $previousNoteId = StateManager::get('previous_noteId');

        // Уведомляем AppLayout об изменении состояния
        $this->dispatch('navigateTo', section: $previousSection, folderId: $previousFolderId, noteId: $previousNoteId);
        // Уведомляем NavigationSidebar об изменении состояния
        $this->dispatch('stateUpdated', section: $previousSection, folderId: $previousFolderId, noteId: $previousNoteId);
    }
}
