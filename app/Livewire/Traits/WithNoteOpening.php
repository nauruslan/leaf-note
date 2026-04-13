<?php

namespace App\Livewire\Traits;

use App\Models\Note;
use Illuminate\Support\Facades\Auth;

trait WithNoteOpening
{
    public function openNote(int $noteId): void
    {
        $note = Note::where('user_id', Auth::id())->find($noteId);

        if (!$note) {
            $this->dispatch('notification', title: 'Ошибка', content: 'Переход не состоялся', type: 'danger');
            return;
        }

        $section = $note->type === Note::TYPE_CHECKLIST ? 'edit-checklist' : 'edit-note';

        $this->dispatch('navigateTo', section: $section, noteId: $noteId);
        $this->dispatch('notification', title: 'Успешно', content: 'Переход к заметке', type: 'success');
        $this->js('window.scrollTo(0, 0)');
    }
}