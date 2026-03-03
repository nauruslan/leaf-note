<?php

namespace App\Livewire\Content;

use App\Models\Note;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ContentCreateNote extends Component
{
    public string $title = '';
    public $content = '';
    public $pendingFolderId = null;
    public string $pendingColor = 'default';

    protected $listeners = [
        'saveNote' => 'triggerSave',
        'editorContent' => 'setContent',
    ];

    public function triggerSave($folderId = null, $color = 'default'): void
    {
        $this->pendingFolderId = $folderId;
        $this->pendingColor = $color;
        $this->dispatch('getEditorContent');
    }

    public function setContent($content): void
    {
        $this->content = $content;
        $this->performSave();
    }

    private function performSave(): void
    {
        try {
            $this->validate([
                'title' => 'required|string|max:255',
                'content' => 'required',
            ]);
            $note = new Note();
            $note->title = $this->title;
            $note->type = Note::TYPE_NOTE;
            $note->payload = $this->content;
            $note->color = $this->pendingColor ?? 'default';
            $note->user_id = Auth::id();

            if ($this->pendingFolderId) {
                $note->folder_id = $this->pendingFolderId;
            } else {
                $note->archive_id = Auth::user()->archive->id;
            }

            $note->save();

            $this->reset(['title', 'content']);
            $this->pendingFolderId = null;
            $this->pendingColor = 'default';

            $this->dispatch('noteSaved');

            $this->dispatch('navigateTo', 'dashboard');

        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('showError', 'Не удалось сохранить заметку');
        }
        $this->dispatch('navigateTo', section: 'dashboard');
    }

    public function render()
    {
        return view('livewire.content.create-note');
    }
}
