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
        if (! empty($this->folderId)) {
            StateManager::set('preset_folder_id', $this->folderId);
        }
        if (! empty($this->safe)) {
            StateManager::set('preset_safe_id', $this->safe->id);
        }
        if (! empty($this->archive)) {
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
