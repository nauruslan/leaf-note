<?php

namespace App\Livewire;

use App\Livewire\Traits\WithBackSection;
use App\Livewire\Traits\WithFavorite;
use App\Livewire\Traits\WithNoteStore;
use App\Models\Archive;
use App\Models\Folder;
use App\Models\Note;
use App\Models\Safe;
use App\Services\StateManager;
use App\Services\TemporaryImageService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class CreateNoteView extends Component
{
    use WithBackSection;
    use WithNoteStore;
    use WithFavorite;

    public $heading='Создать заметку';
    public $section='create-note';

    private const EMPTY_NOTE_STRUCTURE = '{"type":"doc","content":[{"type":"paragraph"}]}';

    public string $title = '';
    public ?int $folderId = null;
    public ?int $safeId = null;
    public ?int $archiveId = null;
    public ?string $dropdownValue = null;
    public bool $is_favorite = false;
    public string $content = '';
    public bool $isSaving = false;
    public ?int $noteId = null;
    public bool $isFirstSave = true;

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

        // Очищаем список временных изображений при входе на страницу создания заметки
        // Это гарантирует, что в сессии будут только изображения, загруженные в текущей сессии
        $temporaryImageService = app(TemporaryImageService::class);
        $temporaryImageService->clear();

        // Если заметка уже создана, загружаем оригинальные пути изображений
        if ($this->noteId) {
            $note = Note::where('user_id', Auth::id())
                ->where('type', Note::TYPE_NOTE)
                ->find($this->noteId);
            if ($note && $note->content) {
                $this->originalImagePaths = $this->extractImagePathsFromContent($note->content);
            }
        }

        // Обработка предустановки флага "Избранное"
        $presetIsFavorite = StateManager::get('preset_is_favorite', false);
        if ($presetIsFavorite) {
            $this->is_favorite = true;
            StateManager::remove('preset_is_favorite');
        }

        $presetSafeId = StateManager::get('preset_safe_id');
        if ($presetSafeId) {
            $this->safeId = $presetSafeId;
            $this->dropdownValue = 'safe_' . $presetSafeId;
            StateManager::remove('preset_safe_id');
            return;
        }

        $presetArchiveId = StateManager::get('preset_archive_id');
        if ($presetArchiveId) {
            $this->archiveId = $presetArchiveId;
            $this->dropdownValue = 'archive_' . $presetArchiveId;
            StateManager::remove('preset_archive_id');
            return;
        }

        $presetFolderId = StateManager::get('preset_folder_id');
        if ($presetFolderId) {
            $this->folderId = $presetFolderId;
            $this->dropdownValue = (string) $presetFolderId;
            StateManager::remove('preset_folder_id');
        }
    }

    public function cancel(): void
    {
        // Если заметка была создана через автосохранение, удаляем её
        if ($this->noteId) {
            $note = Note::where('user_id', Auth::id())
                ->where('type', Note::TYPE_NOTE)
                ->find($this->noteId);

            if ($note) {
                // Удаляем изображения из хранилища
                $imagePaths = $this->extractImagePathsFromContent($note->content);
                $this->deleteImagesFromStorage($imagePaths);
                // Удаляем заметку
                $note->delete();
            }
        }

        // Выполняем отложенное удаление изображений и очищаем временные
        $temporaryImageService = app(TemporaryImageService::class);
        $temporaryImageService->executePendingDeletion();
        $temporaryImageService->deleteUnsavedImages();

        $this->dispatch('navigateTo', 'dashboard');
        // Обновляем sidebar
        $this->dispatch('refreshSidebar');
    }


    public function updatedDropdownValue(): void
    {
        // Обработка префиксов safe_ и archive_
        if (is_string($this->dropdownValue)) {
            if (str_starts_with($this->dropdownValue, 'safe_')) {
                $this->safeId = (int) substr($this->dropdownValue, 5);
                $this->folderId = null;
                $this->archiveId = null;
            } elseif (str_starts_with($this->dropdownValue, 'archive_')) {
                $this->archiveId = (int) substr($this->dropdownValue, 8);
                $this->folderId = null;
                $this->safeId = null;
            } elseif (is_numeric($this->dropdownValue)) {
                $this->folderId = (int) $this->dropdownValue;
                $this->safeId = null;
                $this->archiveId = null;
            }
        }
        $this->autoSave();
    }

    public function updatedFolderId(): void
    {
        // Этот метод может вызываться если folderId изменяется напрямую
        $this->autoSave();
    }

    #[On('updateSafeId')]
    public function setSafeId(int $id): void
    {
        $this->safeId = $id;
        $this->folderId = null;
        $this->archiveId = null;
        $this->autoSave();
    }

    #[On('updateArchiveId')]
    public function setArchiveId(int $id): void
    {
        $this->archiveId = $id;
        $this->folderId = null;
        $this->safeId = null;
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
    public function handleContentReady($data): void
    {
        // Извлекаем контент из массива, если он обернут (из JavaScript приходит { content: ... })
        if (is_array($data) && isset($data['content'])) {
            $content = $data['content'];
        } else {
            $content = $data;
        }

        $this->content = $content;
        $this->autoSave();
    }

    public function autoSave(): void
    {
        // Если выбранный folderId является сейфом, перемещаем его в safeId
        if ($this->folderId && $this->isSafeSelected($this->folderId)) {
            $this->safeId = $this->folderId;
            $this->folderId = null;
        }

        // Если выбранный folderId является архивом, перемещаем его в archiveId
        if ($this->folderId && $this->isArchiveSelected($this->folderId)) {
            $this->archiveId = $this->folderId;
            $this->folderId = null;
        }

        // Условия автосохранения:
        // 1. Должна быть выбрана папка (folderId) ИЛИ сейф (safeId) ИЛИ архив (archiveId)
        // 2. Title должен иметь длину хотя бы 1 символ
        if (($this->folderId === null && $this->safeId === null && $this->archiveId === null) || trim($this->title) === '') {
            return;
        }

        // Проверка уникальности title (исключая текущую заметку если она уже создана)
        if ($this->isTitleExists(trim($this->title), $this->noteId)) {
            $this->dispatch('notification', ['title' => 'Внимание', 'content' => 'Заметка с таким названием уже есть. Чтобы избежать путаницы измените название.', 'type' => 'warning']);
        }

        try {
            $this->validateOnly('title');
        } catch (\Illuminate\Validation\ValidationException) {
            // При автосохранении не показываем ошибку, просто пропускаем
            return;
        }

        $this->isSaving = true;

        try {
            // Если заметка уже создана, обновляем ее
            if ($this->noteId) {
                if (!$this->cachedNote) {
                    $this->cachedNote = Note::where('user_id', Auth::id())
                        ->where('type', Note::TYPE_NOTE)
                        ->find($this->noteId);
                }

                if (!$this->cachedNote) {
                    $this->isSaving = false;
                    return;
                }

                // Выполняем отложенное удаление изображений
                $temporaryImageService = app(TemporaryImageService::class);
                $temporaryImageService->executePendingDeletion();

                $this->updateTitle($this->cachedNote);
                $this->updateContent($this->cachedNote);
                $this->updateLocation($this->cachedNote);
                $this->updateFavorite($this->cachedNote);
                $this->cachedNote->save();

                // Обновляем оригинальные пути изображений для текущего запроса
                $currentImagePaths = $this->extractImagePathsFromContent($this->content);
                $this->originalImagePaths = $currentImagePaths;
            } else {
                // Создаем новую заметку (первое сохранение)
                $note = new Note();
                $note->title = trim($this->title);
                $note->type = Note::TYPE_NOTE;
                $note->content = $this->normalizeContent($this->content);
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
                } elseif ($this->archiveId !== null) {
                    $note->archive_id = $this->archiveId;
                    $note->folder_id = null;
                    $note->safe_id = null;
                } else {
                    // Не должно происходить, т.к. проверка выше
                    $this->isSaving = false;
                    return;
                }

                $note->save();

                // Сохраняем ID созданной заметки для будущих обновлений
                $this->noteId = $note->id;
                $this->cachedNote = $note;
                // Инициализируем оригинальные пути изображений после создания
                $this->originalImagePaths = $this->extractImagePathsFromContent($this->content);

                // Показываем уведомление о первом сохранении только если это первое сохранение
                if ($this->isFirstSave) {
                    $locationName = $this->getLocationName($note);
                    $this->dispatch('notification', ['title' => 'Сохранено', 'content' => "Заметка сохранена в «{$locationName}»", 'type' => 'success']);
                    $this->isFirstSave = false;
                }
            }

            // Очищаем список временных изображений при успешном автосохранении
            // (файлы уже привязаны к заметке и не должны удаляться при уходе со страницы)
            $temporaryImageService = app(TemporaryImageService::class);
            $temporaryImageService->clear();
        } catch (\Throwable $e) {
            report($e);
            // При автосохранении не показываем ошибку пользователю
        } finally {
            $this->isSaving = false;
            // Обновляем sidebar
            $this->dispatch('refreshSidebar');
        }
    }

    private function extractImagePathsFromContent($content): array
    {
        if (is_string($content)) {
            $content = json_decode($content, true);
        }

        if (!is_array($content) || !isset($content['content'])) {
            return [];
        }

        $paths = [];
        $this->traverseContent($content['content'], $paths);

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
        $note->content = $this->content;
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
        } elseif ($this->archiveId !== null) {
            $note->archive_id = $this->archiveId;
            $note->folder_id = null;
            $note->safe_id = null;
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

        // Проверяем с префиксом safe_
        return collect($this->safes)->contains('value', 'safe_' . $selectedId);
    }

    private function isArchiveSelected(?int $selectedId): bool
    {
        if ($selectedId === null) {
            return false;
        }

        // Проверяем с префиксом archive_
        return collect($this->archives)->contains('value', 'archive_' . $selectedId);
    }

    /**
     * Получить название места хранения заметки
     */
    private function getLocationName(Note $note): string
    {
        if ($note->folder_id !== null) {
            $folder = Folder::find($note->folder_id);
            return $folder?->title ?? 'Папка';
        }

        if ($note->safe_id !== null) {
            $safe = Safe::find($note->safe_id);
            return $safe?->name ?? 'Сейф';
        }

        if ($note->archive_id !== null) {
            $archive = Archive::find($note->archive_id);
            return $archive?->name ?? 'Архив';
        }

        return 'Архив';
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

    /**
     * Проверить существование заметки с указанным названием у текущего пользователя
     */
    private function isTitleExists(string $title, ?int $excludeNoteId = null): bool
    {
        $query = Note::where('user_id', Auth::id())
            ->where('type', Note::TYPE_NOTE)
            ->where('title', $title)
            ->whereNull('trash_id');

        if ($excludeNoteId) {
            $query->where('id', '!=', $excludeNoteId);
        }

        return $query->exists();
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.create-note');
    }
}