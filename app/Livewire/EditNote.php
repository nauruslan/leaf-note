<?php

namespace App\Livewire;

use App\Livewire\Traits\WithFolderSafeSelection;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class EditNote extends Component
{
    use WithFolderSafeSelection;

    public ?int $noteId = null;
    public string $title = '';
    public ?int $folderId = null;
    public ?int $safeId = null;
    public $content = '';
    public ?Note $note = null;
    public bool $isLoaded = false;
    public bool $confirmingDeletion = false;
    public array $originalImagePaths = [];

    public ?int $pendingFolderId = null;
    public bool $is_favorite = false;

    protected $listeners = [
        'updateFolderId' => 'setFolderId',
        'updateSafeId' => 'setSafeId',
        'noteUpdated' => 'onNoteUpdated',
        'saveNote' => 'triggerSave',
        'editorContent' => 'setContent',
        'openNote' => 'openNote',
        'navigateTo' => 'handleNavigateTo',
        'noteLoaded' => 'handleNoteLoaded',
    ];

    public function mount(?int $noteId = null, ?int $folderId = null): void
    {
        $this->noteId = $noteId ?? $folderId;

        if ($this->noteId) {
            $this->loadNote();
        }
    }

    public function handleNavigateTo(string $section, ?int $folderId = null): void
    {
        if ($section === 'note' && $folderId) {
            $this->openNote($folderId);
        }
    }

    public function openNote($noteId): void
    {
        $this->noteId = $noteId;
        $this->loadNote();
    }

    public function loadNote(): void
    {
        if (!$this->noteId) {
            return;
        }

        $note = Note::where('user_id', Auth::id())->find($this->noteId);

        if (!$note) {
            return;
        }

        $this->title = $note->title;
        $this->folderId = $note->folder_id;
        $this->safeId = $note->safe_id;
        $this->is_favorite = (bool) $note->is_favorite;
        $this->content = $note->payload;
        $this->originalImagePaths = $this->extractImagePathsFromPayload($note->payload);
        $this->isLoaded = true;

        $this->dispatch('noteLoaded',
            content: $this->content,
            originalImagePaths: $this->originalImagePaths
        );
    }

    private function extractImagePathsFromPayload($payload): array
    {
        if (is_string($payload)) {
            $payload = json_decode($payload, true);
        }

        if (!is_array($payload) || !isset($payload['content'])) {
            return [];
        }

        $paths = [];
        $this->traverseContent($payload['content'], $paths);
        return array_values(array_unique($paths));
    }

    private function traverseContent($nodes, &$paths): void
    {
        if (!is_array($nodes)) {
            return;
        }

        foreach ($nodes as $node) {
            if (!is_array($node)) {
                continue;
            }

            if (($node['type'] ?? null) === 'image' && isset($node['attrs']['path'])) {
                $paths[] = $node['attrs']['path'];
            }

            if (isset($node['content']) && is_array($node['content'])) {
                $this->traverseContent($node['content'], $paths);
            }
        }
    }

    public function onNoteUpdated(): void
    {
        $this->dispatch('navigateTo', 'dashboard');
    }

    public function cancel(): void
    {
        $this->js('localStorage.clear()');
        $this->dispatch('restoreNoteOriginalState');
        $this->dispatch('navigateTo', 'dashboard');
    }

    public function confirmDelete(): void
    {
        $note = $this->note;

        if (!$note) {
            $this->dispatch('showError', 'Заметка не найдена');
            return;
        }

        if ($note->is_favorite) {
            $note->toggleFavorite();
        }

        if (!$note->moveToTrash()) {
            $this->dispatch('showError', 'Не удалось удалить заметку');
            return;
        }

        $this->dispatch('noteUpdated');
        $this->dispatch('navigateTo', 'dashboard');
    }

    public function confirmDeletion(): void
    {
        $this->confirmingDeletion = true;
    }

    public function closeModal(): void
    {
        $this->confirmingDeletion = false;
    }

    public function openDeleteModal(): void
    {
        $this->confirmDeletion();
    }

    public function triggerSave($folderId = null): void
    {
        $selectedId = $folderId ?? $this->folderId;

        if ($this->isSafeSelected($selectedId)) {
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
            if (!$this->validateNote()) {
                return;
            }

            $currentImagePaths = $this->extractImagePathsFromPayload($this->content);
            $removedImagePaths = array_diff($this->originalImagePaths, $currentImagePaths);
            $this->deleteImagesFromStorage($removedImagePaths);

            $this->updateNoteLocation();
            $this->originalImagePaths = $currentImagePaths;

            $this->dispatch('noteUpdated');
            $this->dispatch('navigateTo', 'dashboard');

        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('showError', 'Не удалось сохранить заметку');
        }
    }

    private function validateNote(): bool
    {
        if (empty(trim($this->title))) {
            $this->dispatch('showError', 'Название обязательно');
            return false;
        }

        if (strlen($this->title) > 255) {
            $this->dispatch('showError', 'Название слишком длинное');
            return false;
        }

        return true;
    }

    private function updateNoteLocation(): void
    {
        $note = $this->note;

        if (!$note) {
            $this->dispatch('showError', 'Заметка не найдена');
            return;
        }

        $note->title = $this->title;
        $note->payload = $this->content;
        $note->is_favorite = $this->is_favorite;

        if ($this->pendingFolderId !== null) {
            $note->folder_id = $this->pendingFolderId;
            $note->safe_id = null;
            $note->archive_id = null;
        } elseif ($this->safeId !== null) {
            $note->safe_id = $this->safeId;
            $note->folder_id = null;
            $note->archive_id = null;
        }

        $note->save();
    }

    private function isSafeSelected(?int $folderId): bool
    {
        if ($folderId === null) {
            return false;
        }

        return collect($this->safes)->contains('value', $folderId);
    }

    public function toggleFavorite(): void
    {
        if (!$this->note) {
            return;
        }

        $wasFavorite = $this->is_favorite;
        $this->is_favorite = !$this->is_favorite;

        $this->dispatch('favoriteToggled',
            noteId: $this->note->id,
            isFavorite: $this->is_favorite,
            wasFavorite: $wasFavorite
        );
    }

    private function deleteImagesFromStorage(array $paths): void
    {
        foreach ($paths as $path) {
            try {
                $cleanPath = str_replace('..', '', $path);

                if (str_starts_with($cleanPath, 'notes/') &&
                    Storage::disk('public')->exists($cleanPath)) {
                    Storage::disk('public')->delete($cleanPath);
                }
            } catch (\Exception $e) {
                report($e);
            }
        }
    }

    public function render()
    {
        return view('livewire.edit-note');
    }
}