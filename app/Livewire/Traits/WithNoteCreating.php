<?php

namespace App\Livewire\Traits;

use App\Services\StateManager;

trait WithNoteCreating
{
    /**
     * Установить пресеты для создания заметки.
     */
    protected function setCreationPresets(): void
    {
        if (isset($this->folderId) && $this->folderId) {
            StateManager::set('preset_folder_id', $this->folderId);
        }
        if (isset($this->safe) && $this->safe) {
            StateManager::set('preset_safe_id', $this->safe->id);
        }
        if (isset($this->archive) && $this->archive) {
            StateManager::set('preset_archive_id', $this->archive->id);
        }
    }

    public function createNote(): void
    {
        $this->setCreationPresets();
        $this->dispatch('navigateTo', 'create-note');
    }

    public function createChecklist(): void
    {
        $this->setCreationPresets();
        $this->dispatch('navigateTo', 'create-checklist');
    }
}