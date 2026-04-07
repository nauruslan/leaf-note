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

    private const EMPTY_NOTE_STRUCTURE = '{"type":"doc","content":[{"type":"paragraph"}]}';

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

    private ?Note $cachedNote = null;

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
        ];
    }

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

        $this->cachedNote = $note;
        $this->title = $note->title;
        $this->folderId = $note->folder_id;
        $this->safeId = $note->safe_id;
        $this->is_favorite = (bool) $note->is_favorite;
        $this->content = $note->payload; // Оригинальная JSON строка
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
        $this->content = $this->normalizeContent($content);
        $this->performSave();
    }

    public function updatedTitle(): void
    {
        $this->autoSave();
    }

    public function updatedContent(): void
    {
        // Нормализуем контент перед автосохранением
        $this->content = $this->normalizeContent($this->content);
        $this->autoSave();
    }

    public function updatedFolderId(): void
    {
        $this->autoSave();
    }

    public function updatedSafeId(): void
    {
        $this->autoSave();
    }

    public function autoSave(): void
    {
        if (!$this->noteId) {
            return;
        }

        // Если выбранный folderId является сейфом, перемещаем его в safeId
        if ($this->folderId && $this->isSafeSelected($this->folderId)) {
            $this->safeId = $this->folderId;
            $this->folderId = null;
        }

        try {
            $this->validateOnly('title');
        } catch (\Illuminate\Validation\ValidationException) {
            // При автосохранении не показываем ошибку, просто пропускаем
            return;
        }

        try {
            // Перезагружаем из БД если кэш пуст
            if (!$this->cachedNote) {
                $this->cachedNote = Note::where('user_id', Auth::id())
                    ->where('type', Note::TYPE_NOTE)
                    ->find($this->noteId);
            }

            if (!$this->cachedNote) {
                return;
            }

            // Удаление изображений, которые больше не используются
            $currentImagePaths = $this->extractImagePathsFromPayload($this->content);
            $removedImagePaths = array_diff($this->originalImagePaths, $currentImagePaths);
            $this->deleteImagesFromStorage($removedImagePaths);

            $this->updateTitle($this->cachedNote);
            $this->updateContent($this->cachedNote);
            $this->updateLocation($this->cachedNote);

            $this->cachedNote->save();

            // Обновляем оригинальные пути изображений
            $this->originalImagePaths = $currentImagePaths;

            // Можно диспатчить событие для UI, что автосохранение прошло успешно
            // $this->dispatch('autosaveCompleted');
        } catch (\Throwable $e) {
            report($e);
            // При автосохранении не показываем ошибку пользователю
        }
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
        if (!$this->cachedNote) {
            $this->cachedNote = Note::where('user_id', Auth::id())->find($this->noteId);
        }

        if (!$this->cachedNote) {
            $this->dispatch('showError', 'Заметка не найдена');
            return;
        }

        $this->updateTitle($this->cachedNote);
        $this->updateContent($this->cachedNote);
        $this->updateLocation($this->cachedNote);
        $this->cachedNote->is_favorite = $this->is_favorite;

        $this->cachedNote->save();
    }

    private function normalizeContent(mixed $content): string
    {
        if (is_string($content) && $content === '') {
            return self::EMPTY_NOTE_STRUCTURE;
        }

        if (! is_string($content)) {
            if (is_array($content) || is_object($content)) {
                $content = json_encode($content);
            } else {
                return self::EMPTY_NOTE_STRUCTURE;
            }
        }

        try {
            $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

            if (! is_array($decoded) || empty($decoded)) {
                return self::EMPTY_NOTE_STRUCTURE;
            }

            return json_encode($decoded, JSON_UNESCAPED_UNICODE);
        } catch (\JsonException) {
            return self::EMPTY_NOTE_STRUCTURE;
        }
    }

    private function updateTitle(Note $note): void
    {
        $note->title = trim($this->title);
    }

    private function updateContent(Note $note): void
    {
        $note->payload = $this->content;
    }

    private function updateLocation(Note $note): void
    {
        if ($this->folderId !== null) {
            $note->folder_id = $this->folderId;
            $note->safe_id = null;
            $note->archive_id = null;
        } elseif ($this->safeId !== null) {
            $note->safe_id = $this->safeId;
            $note->folder_id = null;
            $note->archive_id = null;
        } else {
            $note->folder_id = null;
            $note->safe_id = null;
            $note->archive_id = null;
        }
    }

    private function isSafeSelected(?int $folderId): bool
    {
        if ($folderId === null) {
            return false;
        }

        return collect($this->safes)->contains('value', $folderId);
    }

    public function saveWithLocation(): void
    {
        if ($this->isSafeSelected($this->folderId)) {
            $this->safeId = $this->folderId;
            $this->folderId = null;
        }

        $this->save();
    }

    public function save(): void
    {
        $this->dispatch('getEditorContent');
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
