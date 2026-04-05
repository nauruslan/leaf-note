<?php

namespace App\Livewire;

use App\Models\Folder;
use App\Models\Note;
use App\Models\Safe;
use App\Services\StateManager;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CreateChecklistView extends Component
{
    public string $title = '';
    public ?int $folderId = null;
    public ?int $safeId = null;
    public bool $is_favorite = false;
    public $content = '';
    public $folders = [];
    public $safes = [];
    private bool $isSaving = false;


    protected $listeners = [
        'updateFolderId' => 'setFolderId',
        'updateSafeId' => 'setSafeId',
        'checklistSaved' => 'onChecklistSaved',
        'checklistLoaded' => 'handleChecklistLoaded',
        'checklistContentReady' => 'handleContentReady',
    ];

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

        $this->safes = Safe::where('user_id', Auth::id())
            ->get()
            ->map(fn($safe) => ['value' => $safe->id, 'text' => 'Сейф']);

        // Проверяем preset_safe_id (переданный при открытии из сейфа)
        $presetSafeId = StateManager::get('preset_safe_id');
        if ($presetSafeId) {
            $this->folderId = $presetSafeId;
            $this->safeId = $presetSafeId;
            // Очищаем preset после использования
            StateManager::remove('preset_safe_id');
            return;
        }

        // Проверяем preset_folder_id (переданный при открытии из папки)
        $presetFolderId = StateManager::get('preset_folder_id');
        if ($presetFolderId) {
            $this->folderId = $presetFolderId;
            // Очищаем preset после использования
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

        // Check if the selected ID is a safe
        $isSafe = collect($this->safes)->contains('value', $this->folderId);

        if ($isSafe) {
            $this->safeId = $this->folderId;
            $this->folderId = null;
        }

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
                $note->safe_id = null;
            } elseif ($this->safeId) {
                $note->safe_id = $this->safeId;
                $note->folder_id = null;
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

        // Для нового списка ещё нет ID, поэтому диспатчим без noteId
        $this->dispatch('favoriteToggled',
            noteId: null,
            isFavorite: $this->is_favorite,
            wasFavorite: !$this->is_favorite
        );
    }

    public function render()
    {
        return view('livewire.create-checklist');
    }
}