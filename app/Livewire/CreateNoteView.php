<?php

namespace App\Livewire;

use App\Livewire\Traits\WithFavorite;
use App\Livewire\Traits\WithFolderSafeSelection;
use App\Models\Note;
use App\Models\Safe;
use App\Services\StateManager;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class CreateNoteView extends Component
{
    use WithFavorite;
    use WithFolderSafeSelection;

    private const EMPTY_NOTE_STRUCTURE = '{"type":"doc","content":[{"type":"paragraph"}]}';

    public string $title = '';
    public ?int $folderId = null;
    public ?int $safeId = null;
    public bool $is_favorite = false;
    public string $content = '';
    public bool $isSaving = false;
    public ?int $noteId = null;

    private ?Note $cachedNote = null;
    private array $originalImagePaths = [];

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
        ];
    }

    public function mount(): void
    {
        $this->content = self::EMPTY_NOTE_STRUCTURE;

        // Если заметка уже создана (например, после автосохранения), загружаем оригинальные пути изображений
        if ($this->noteId) {
            $note = Note::where('user_id', Auth::id())
                ->where('type', Note::TYPE_NOTE)
                ->find($this->noteId);
            if ($note && $note->payload) {
                $this->originalImagePaths = $this->extractImagePathsFromPayload($note->payload);
            }
        }

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

    public function cancel(): void
    {
        $this->dispatch('deleteUploadedImages');
        $this->dispatch('navigateTo', 'dashboard');
    }

    public function toggleFavorite(): void
    {
        // Если заметка уже создана, обновляем в БД и синхронизируем состояние
        if ($this->noteId) {
            // Используем метод из трейта WithFavorite через явный вызов
            $note = $this->callTraitToggleFavorite($this->noteId);
            if ($note) {
                // Синхронизируем локальное свойство с состоянием из БД
                $this->is_favorite = $note->is_favorite;
            }
        } else {
            // Иначе просто меняем локальное свойство, которое сохранится при создании заметки
            $this->is_favorite = !$this->is_favorite;

            // Вызываем автосохранение, если выполнены условия (есть папка/сейф и заголовок)
            $this->autoSave();
        }
    }

    private function callTraitToggleFavorite(int $noteId): ?\App\Models\Note
    {
        // Явный вызов метода из трейта WithFavorite
        $note = \App\Models\Note::where('user_id', \Illuminate\Support\Facades\Auth::id())->find($noteId);

        if ($note) {
            $note->toggleFavorite();
            if ($note->is_favorite) {
                $this->dispatch('notification', title: 'Успешно', content: 'Добавлено в избранное', type: 'success');
            } else {
                $this->dispatch('notification', title: 'Успешно', content: 'Удалено из избранного', type: 'success');
            }
            $this->dispatch('refreshSidebar');
        }

        return $note;
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
        try {
            $this->validateOnly('title');
        } catch (\Illuminate\Validation\ValidationException) {
            $this->dispatch('showError', 'Название обязательно и не должно превышать 255 символов');
            return;
        }

        try {
            // Если заметка уже создана через автосохранение, обновляем ее
            if ($this->noteId) {
                if (!$this->cachedNote) {
                    $this->cachedNote = Note::where('user_id', Auth::id())
                        ->where('type', Note::TYPE_NOTE)
                        ->find($this->noteId);
                }

                if (!$this->cachedNote) {
                    $this->dispatch('notification', title: 'Ошибка', content: 'Заметка не найдена', type: 'danger');
                    return;
                }

                // Удаление изображений, которые больше не используются
                $currentImagePaths = $this->extractImagePathsFromPayload($this->content);
                // Получаем оригинальные пути из БД (из сохраненного payload)
                $originalImagePathsFromDb = $this->extractImagePathsFromPayload($this->cachedNote->payload);
                $removedImagePaths = array_diff($originalImagePathsFromDb, $currentImagePaths);

                $this->deleteImagesFromStorage($removedImagePaths);

                $this->updateTitle($this->cachedNote);
                $this->updateContent($this->cachedNote);
                $this->updateLocation($this->cachedNote);
                $this->updateFavorite($this->cachedNote);
                $this->cachedNote->save();

                // Обновляем оригинальные пути изображений для текущего запроса
                $this->originalImagePaths = $currentImagePaths;
            } else {
                // Создаем новую заметку
                $note = new Note();
                $note->title = trim($this->title);
                $note->type = Note::TYPE_NOTE;
                $note->payload = $this->normalizeContent($this->content);
                $note->is_favorite = $this->is_favorite;
                $note->user_id = Auth::id();

                if ($this->folderId !== null) {
                    $note->folder_id = $this->folderId;
                    $note->safe_id = null;
                    $note->archive_id = null;
                } elseif ($this->safeId !== null) {
                    $note->safe_id = $this->safeId;
                    $note->folder_id = null;
                    $note->archive_id = null;
                } else {
                    $note->archive_id = Auth::user()->archive->id;
                }

                $note->save();
                $this->noteId = $note->id;
                $this->cachedNote = $note;
                // Инициализируем оригинальные пути изображений после создания
                $this->originalImagePaths = $this->extractImagePathsFromPayload($this->content);
            }

            $this->dispatch('noteCreated');
            $this->dispatch('navigateTo', 'dashboard');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('notification', title: 'Ошибка', content: 'Не удалось сохранить заметку', type: 'danger');
        }
    }

    public function updatedFolderId(): void
    {
        $this->autoSave();
    }

    #[On('updateSafeId')]
    public function setSafeId(int $id): void
    {
        $this->safeId = $id;
        $this->folderId = null;
        $this->autoSave();
    }

    public function updatedSafeId(): void
    {
        $this->autoSave();
    }

    public function updatedTitle(): void
    {
        $this->autoSave();
    }

    public function updatedContent(): void
    {
        $this->autoSave();
    }

    #[On('noteContentReady')]
    public function handleContentReady(string $content): void
    {
        $this->content = $content;
        $this->save();
    }

    public function autoSave(): void
    {
        // Если выбранный folderId является сейфом, перемещаем его в safeId
        if ($this->folderId && $this->isSafeSelected($this->folderId)) {
            $this->safeId = $this->folderId;
            $this->folderId = null;
        }

        // Условия автосохранения:
        // 1. Должна быть выбрана папка (folderId) ИЛИ сейф (safeId)
        // 2. Title должен иметь длину хотя бы 1 символ
        if (($this->folderId === null && $this->safeId === null) || trim($this->title) === '') {
            return;
        }

        try {
            $this->validateOnly('title');
        } catch (\Illuminate\Validation\ValidationException) {
            // При автосохранении не показываем ошибку, просто пропускаем
            return;
        }

        try {
            // Если заметка уже создана, обновляем ее
            if ($this->noteId) {
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
                // Получаем оригинальные пути из БД (из сохраненного payload)
                $originalImagePathsFromDb = $this->extractImagePathsFromPayload($this->cachedNote->payload);
                $removedImagePaths = array_diff($originalImagePathsFromDb, $currentImagePaths);

                $this->deleteImagesFromStorage($removedImagePaths);

                $this->updateTitle($this->cachedNote);
                $this->updateContent($this->cachedNote);
                $this->updateLocation($this->cachedNote);
                $this->updateFavorite($this->cachedNote);
                $this->cachedNote->save();

                // Обновляем оригинальные пути изображений для текущего запроса
                $this->originalImagePaths = $currentImagePaths;
            } else {
                // Создаем новую заметку
                $note = new Note();
                $note->title = trim($this->title);
                $note->type = Note::TYPE_NOTE;
                $note->payload = $this->normalizeContent($this->content);
                $note->is_favorite = $this->is_favorite;
                $note->user_id = Auth::id();

                if ($this->folderId !== null) {
                    $note->folder_id = $this->folderId;
                    $note->safe_id = null;
                    $note->archive_id = null;
                } elseif ($this->safeId !== null) {
                    $note->safe_id = $this->safeId;
                    $note->folder_id = null;
                    $note->archive_id = null;
                } else {
                    // Не должно происходить, т.к. проверка выше
                    return;
                }

                $note->save();

                // Сохраняем ID созданной заметки для будущих обновлений
                $this->noteId = $note->id;
                $this->cachedNote = $note;
                // Инициализируем оригинальные пути изображений после создания
                $this->originalImagePaths = $this->extractImagePathsFromPayload($this->content);
            }
        } catch (\Throwable $e) {
            report($e);
            // При автосохранении не показываем ошибку пользователю
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

    private function deleteImagesFromStorage(array $paths): void
    {
        foreach ($paths as $path) {
            try {
                $cleanPath = str_replace('..', '', $path);

                if (str_starts_with($cleanPath, 'notes/') &&
                    \Illuminate\Support\Facades\Storage::disk('public')->exists($cleanPath)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($cleanPath);
                }
            } catch (\Exception $e) {
                report($e);
            }
        }
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
            $note->archive_id = Auth::user()->archive->id;
        }
    }

    private function updateFavorite(Note $note): void
    {
        $note->is_favorite = $this->is_favorite;
    }

    private function isSafeSelected(?int $selectedId): bool
    {
        if ($selectedId === null) {
            return false;
        }

        return Safe::where('user_id', Auth::id())->where('id', $selectedId)->exists();
    }

    #[Computed]
    public function note(): ?Note
    {
        return $this->noteId
            ? Note::where('user_id', Auth::id())
                ->where('type', Note::TYPE_NOTE)
                ->find($this->noteId)
            : null;
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.create-note');
    }
}
