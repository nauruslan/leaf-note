<?php

namespace App\Livewire;

use App\Models\Folder;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class NoteView extends Component
{
    public ?int $noteId = null;
    public string $title = '';
    public ?int $folderId = null;
    public $content = '';
    public $folders = [];
    public ?Note $note = null;
    public bool $isLoaded = false;
    public bool $confirmingDeletion = false;
    public array $originalImagePaths = [];

    public ?int $pendingFolderId = null;
    public bool $is_favorite = false;

    protected $listeners = [
        'updateFolderId' => 'setFolderId',
        'noteUpdated' => 'onNoteUpdated',
        'saveNote' => 'triggerSave',
        'editorContent' => 'setContent',
        'openNote' => 'openNote',
        'navigateTo' => 'handleNavigateTo',
        'noteLoaded' => 'handleNoteLoaded',
    ];

    public function handleNoteLoaded(): void
    {
        // Пустой метод для обработки события noteLoaded
    }

    public function mount(?int $noteId = null, ?int $folderId = null): void
    {
        // Для note-view folderId используется как noteId
        $this->noteId = $noteId ?? $folderId;
        $this->folders = Folder::forUser(Auth::user())
            ->active()
            ->orderBy('title')
            ->get();

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

        $this->note = Note::where('user_id', Auth::id())
            ->find($this->noteId);

        if ($this->note) {
            $this->title = $this->note->title;
            $this->folderId = $this->note->folder_id;
            $this->is_favorite = (bool) $this->note->is_favorite;
            $this->content = $this->note->payload;
            $this->originalImagePaths = $this->extractImagePathsFromPayload($this->note->payload);
            $this->isLoaded = true;

            $this->dispatch('noteLoaded',
                content: $this->content,
                originalImagePaths: $this->originalImagePaths
            );
        }
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

    public function setFolderId($id): void
    {
        $this->folderId = $id;
    }

    public function save(): void
    {
        $this->js('localStorage.clear()');

        $this->dispatch('saveNote',
            folderId: $this->folderId
        );
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
        if (!$this->note) {
            $this->dispatch('showError', 'Заметка не найдена');
            return;
        }

        if ($this->note->is_favorite) {
            $this->note->update(['is_favorite' => false]);
        }

        if ($this->note->moveToTrash()) {
            $this->dispatch('noteUpdated');
            $this->dispatch('navigateTo', 'dashboard');
        } else {
            $this->dispatch('showError', 'Не удалось удалить заметку');
        }
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
        // Для обратной совместимости
        $this->confirmDeletion();
    }

    public function triggerSave($folderId = null): void
    {
        $this->pendingFolderId = $folderId ?? $this->folderId;
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

            if (!$this->note) {
                $this->dispatch('showError', 'Заметка не найдена');
                return;
            }

            $currentImagePaths = $this->extractImagePathsFromPayload($this->content);

            $removedImagePaths = array_diff($this->originalImagePaths, $currentImagePaths);

            $this->deleteImagesFromStorage($removedImagePaths);

            $this->note->title = $this->title;
            $this->note->payload = $this->content;
            $this->note->is_favorite = $this->is_favorite;

            if ($this->pendingFolderId !== null) {
                $this->note->folder_id = $this->pendingFolderId;
            }

            $this->note->save();

            $this->originalImagePaths = $currentImagePaths;

            $this->dispatch('noteUpdated');
            $this->dispatch('navigateTo', 'dashboard');

        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('showError', 'Не удалось сохранить заметку');
        }
    }

    public function toggleFavorite(): void
    {
        if (!$this->note) {
            return;
        }

        $this->is_favorite = !$this->is_favorite;
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
        return view('livewire.note-view');
    }
}