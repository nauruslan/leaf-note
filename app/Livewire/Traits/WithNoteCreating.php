<?php

namespace App\Livewire\Traits;

use App\Services\StateManager;

trait WithNoteCreating
{
    public function createNote(): void
    {
        if (isset($this->folderId) && $this->folderId) {
            StateManager::set('preset_folder_id', $this->folderId);
        }
        if (isset($this->safe) && $this->safe) {
            StateManager::set('preset_safe_id', $this->safe->id);
        }
        $this->dispatch('navigateTo', 'create-note');
    }


    public function createChecklist(): void
    {
        if (isset($this->folderId) && $this->folderId) {
            StateManager::set('preset_folder_id', $this->folderId);
        }
        if (isset($this->safe) && $this->safe) {
            StateManager::set('preset_safe_id', $this->safe->id);
        }
        $this->dispatch('navigateTo', 'create-checklist');
    }

}
