<?php

namespace App\Livewire;

use App\Models\Folder;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CreateChecklistView extends Component
{
    public string $title = '';
    public ?int $folderId = null;
    public bool $is_favorite = false;
    public $content = '';
    public $folders = [];
    private bool $isSaving = false;


    protected $listeners = [
        'updateFolderId' => 'setFolderId',
        'checklistSaved' => 'onChecklistSaved',
        'checklistLoaded' => 'handleChecklistLoaded',
        'checklistContentReady' => 'handleContentReady',
    ];

    public function handleChecklistLoaded(): void
    {
        // Пустой метод для обработки события checklistLoaded
    }

    public function handleContentReady($content): void
    {
        if ($this->isSaving) {
            return;
        }
        $this->isSaving = true;

        $this->content = $content;
        $this->performSave();
    }

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

    public function onChecklistSaved()
    {
        $this->dispatch('navigateTo', 'dashboard');
    }

    public function cancel()
    {
        $this->js('localStorage.clear()');
        $this->dispatch('deleteUploadedImages');
        $this->dispatch('navigateTo', 'dashboard');
    }


    public function prepareAndSave()
    {
        $this->js('localStorage.clear()');

        // Запрашиваем контент у JavaScript через событие
        $this->dispatch('getChecklistContent');
    }

    public function performSave()
    {
        if (is_array($this->content) && count($this->content) === 1) {
            $this->content = reset($this->content);
        }

        if (is_string($this->content) && !empty($this->content)) {
            try {
                $decoded = json_decode($this->content, true, 512, JSON_THROW_ON_ERROR);
                if (is_string($decoded)) {
                    $decoded = json_decode($decoded, true, 512, JSON_THROW_ON_ERROR);
                }
                $this->content = $decoded;
            } catch (\JsonException $e) {
                logger()->error('[CreateChecklist] JSON decode error', ['error' => $e->getMessage()]);
                $this->content = null;
            }
        } else {
            logger()->warning('[CreateChecklist] No content or empty content');
        }

        $this->saveToDatabase();
    }

    private function saveToDatabase(): void
    {
        try {
            $this->validate([
                'title' => 'required|string|max:255',
                'content' => 'required',
            ]);

            $note = new Note();
            $note->title = $this->title;
            $note->type = Note::TYPE_CHECKLIST;
            $note->payload = $this->content;
            $note->is_favorite = $this->is_favorite;
            $note->user_id = Auth::id();

            if ($this->folderId) {
                $note->folder_id = $this->folderId;
            } else {
                $note->archive_id = Auth::user()->archive->id;
            }

            $note->save();

            $this->reset(['title', 'content']);
            $this->isSaving = false;

            $this->js('localStorage.clear()');

            $this->dispatch('checklistCreated');
            $this->dispatch('checklistSaved');
            $this->dispatch('navigateTo', 'dashboard');

        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('showError', 'Не удалось сохранить список');
            $this->isSaving = false;
        }
    }

    public function toggleFavorite(): void
    {
        $this->is_favorite = !$this->is_favorite;
    }

    public function render()
    {
        return view('livewire.create-checklist');
    }
}
