<?php
namespace App\Livewire;

use App\Models\Folder;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CreateNoteView extends Component
{
    public string $title = '';
    public ?int $pendingFolderId = null;
    public ?int $folderId = null;
    public bool $is_favorite = false;
    public $content = '';
    public $folders = [];


    protected $listeners = [
        'updateFolderId' => 'setFolderId',
        'noteSaved' => 'onNoteSaved',
        'saveNote' => 'triggerSave',
        'editorContent' => 'setContent',
    ];

    public function mount()
    {
        $this->folders = Folder::forUser(Auth::user())
            ->active()
            ->orderBy('title')
            ->get();
    }

    public function setFolderId($id)
    {
        $this->folderId = $id;
    }

    public function save()
    {
        $this->js('localStorage.clear()');

        $this->dispatch('saveNote',
            folderId: $this->folderId
        );
    }

    public function onNoteSaved()
    {
        $this->dispatch('navigateTo', 'dashboard');
    }

    public function cancel()
    {
        $this->js('localStorage.clear()');
        $this->dispatch('deleteUploadedImages');
        $this->dispatch('navigateTo', 'dashboard');
    }


    public function saveNote()
    {
        $this->dispatch('noteCreated');
    }


    public function triggerSave($folderId = null): void
    {
        $this->pendingFolderId = $folderId;
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
            $note->is_favorite = $this->is_favorite;
            $note->user_id = Auth::id();

            if ($this->pendingFolderId) {
                $note->folder_id = $this->pendingFolderId;
            } else {
                $note->archive_id = Auth::user()->archive->id;
            }

            $note->save();

            $this->reset(['title', 'content']);
            $this->pendingFolderId = null;

            $this->js('localStorage.clear()');

            $this->dispatch('noteSaved');
            $this->dispatch('navigateTo', 'dashboard');

        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('showError', 'Не удалось сохранить заметку');
        }
    }

    public function toggleFavorite(): void
    {
        $this->is_favorite = !$this->is_favorite;
    }

    public function render()
    {
        return view('livewire.create-note');
    }
}