<?php

namespace App\Livewire\Traits;

use App\Models\Note;
use App\Services\StateManager;
use Illuminate\Support\Facades\Auth;

trait WithNoteOpening
{
    /**
     * Установить пресеты для создания заметки.
     */
    protected function setPresets(): void
    {
        if (! empty($this->folderId)) {
            StateManager::set('preset_folder_id', $this->folderId);
        }
        if (! empty($this->safeId)) {
            StateManager::set('preset_safe_id', $this->safeId);
        }
        if (! empty($this->archiveId)) {
            StateManager::set('preset_archive_id', $this->archiveId);
        }
        // Для избранного используем специальный флаг
        if (isset($this->isFavorite)) {
            StateManager::set('preset_is_favorite', $this->isFavorite ? true : false);
        }
    }

    public function openCreateNotePage(): void
    {
        $this->setPresets();
        $this->dispatch('navigateTo', 'create-note');
    }

    public function openCreateChecklistPage(): void
    {
        $this->setPresets();
        $this->dispatch('navigateTo', 'create-checklist');
    }

    public function openNote(int $noteId): void
    {
        $note = Note::where('user_id', Auth::id())->find($noteId);

        if (!$note) {
            $this->dispatch('notification', ['title' => 'Ошибка', 'content' => 'Переход не состоялся', 'type' => 'danger']);
            return;
        }

        $section = $note->type === Note::TYPE_CHECKLIST ? 'edit-checklist' : 'edit-note';

        $this->dispatch('navigateTo', section: $section, noteId: $noteId);
        $this->js('window.scrollTo(0, 0)');
    }
}
