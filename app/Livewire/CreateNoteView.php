<?php
namespace App\Livewire;

use App\Models\Folder;
use App\Models\Note;
use App\Models\Safe;
use App\Services\StateManager;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CreateNoteView extends Component
{
    public string $title = '';
    public ?int $pendingFolderId = null;
    public ?int $folderId = null;
    public ?int $safeId = null;
    public bool $is_favorite = false;
    public $content = '';
    public $folders = [];
    public $safes = [];


    protected $listeners = [
        'updateFolderId' => 'setFolderId',
        'updateSafeId' => 'setSafeId',
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

        $this->safes = Safe::where('user_id', Auth::id())
            ->get()
            ->map(fn($safe) => ['value' => $safe->id, 'text' => 'Сейф']);

        $presetSafeId = StateManager::get('preset_safe_id');
        if ($presetSafeId) {
            $this->folderId = $presetSafeId;
            $this->safeId = $presetSafeId;
            StateManager::remove('preset_safe_id');
            return;
        }

        $presetFolderId = StateManager::get('preset_folder_id');
        if ($presetFolderId) {
            $this->folderId = $presetFolderId;
            StateManager::remove('preset_folder_id');
        }
    }

    public function setFolderId($id)
    {
        $this->folderId = $id;
    }

    public function setSafeId($id)
    {
        $this->safeId = $id;
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
        $selectedId = $folderId ?? $this->folderId;

        $isSafe = collect($this->safes)->contains('value', $selectedId);

        if ($isSafe) {
            $this->pendingFolderId = null;
            $this->safeId = $selectedId;
        } else {
            $this->pendingFolderId = $selectedId;
        }

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
                $note->safe_id = null;
            } elseif ($this->safeId) {
                $note->safe_id = $this->safeId;
                $note->folder_id = null;
            } else {
                $note->archive_id = Auth::user()->archive->id;
            }

            $note->save();

            $this->reset(['title', 'content']);
            $this->pendingFolderId = null;
            $this->safeId = null;

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

        $this->dispatch('favoriteToggled',
            noteId: null,
            isFavorite: $this->is_favorite,
            wasFavorite: !$this->is_favorite
        );
    }

    public function render()
    {
        return view('livewire.create-note');
    }
}
