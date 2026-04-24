<?php

namespace App\Livewire;

use App\Livewire\Traits\WithBackSection;
use App\Livewire\Traits\WithFavorite;
use App\Livewire\Traits\WithNoteStore;
use App\Models\Archive;
use App\Models\Folder;
use App\Models\Note;
use App\Models\Safe;
use App\Services\TemporaryImageService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class EditNote extends Component
{
    use WithBackSection;
    use WithNoteStore;
    use WithFavorite;

    public $section='edit-note';

    private const EMPTY_NOTE_STRUCTURE = '{"type":"doc","content":[{"type":"paragraph"}]}';

    public ?int $noteId = null;
    public string $title = '';
    public ?int $folderId = null;
    public ?int $safeId = null;
    public ?int $archiveId = null;
    public ?string $dropdownValue = null;
    public $content = '';
    public ?Note $note = null;
    public bool $isLoaded = false;
    public bool $confirmingDeletion = false;
    public array $originalImagePaths = [];

    public ?int $pendingFolderId = null;
    public bool $is_favorite = false;
    public bool $isSaving = false;

    // Для отслеживания изменений местоположения
    public ?int $originalFolderId = null;
    public ?int $originalSafeId = null;
    public ?int $originalArchiveId = null;

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
        'updateArchiveId' => 'setArchiveId',
        'noteUpdated' => 'onNoteUpdated',
        'saveNote' => 'triggerSave',
        'editorContent' => 'setContent',
        'openNote' => 'openNote',
        'navigateTo' => 'handleNavigateTo',
        'noteLoaded' => 'handleNoteLoaded',
    ];

    public function mount(?int $noteId = null, ?int $folderId = null): void
    {
        $this->noteId = $noteId;
        $this->folderId = $folderId;

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
        $this->note = $note;
        $this->title = $note->title;
        $this->folderId = $note->folder_id;
        $this->safeId = $note->safe_id;
        $this->archiveId = $note->archive_id;
        $this->is_favorite = (bool) $note->is_favorite;
        $this->content = $note->content;
        $this->originalImagePaths = $this->extractImagePathsFromContent($note->content);
        $this->isLoaded = true;

        // Сохраняем оригинальное местоположение для отслеживания изменений
        $this->originalFolderId = $note->folder_id;
        $this->originalSafeId = $note->safe_id;
        $this->originalArchiveId = $note->archive_id;

        // Инициализируем dropdownValue в зависимости от того, где находится заметка
        if ($this->safeId) {
            $this->dropdownValue = 'safe_' . $this->safeId;
        } elseif ($this->archiveId) {
            $this->dropdownValue = 'archive_' . $this->archiveId;
        } elseif ($this->folderId) {
            $this->dropdownValue = (string) $this->folderId;
        }

        $this->dispatch('noteLoaded',
            content: $this->content,
            originalImagePaths: $this->originalImagePaths
        );
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

    public function onNoteUpdated(): void
    {
        $this->dispatch('navigateTo', 'dashboard');
    }

    public function cancel(): void
    {
        // Очищаем отложенные удаления изображений (не выполняем их)
        $temporaryImageService = app(TemporaryImageService::class);
        $temporaryImageService->clearPendingDeletion();

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
            // Корзина переполнена
            $this->dispatch('notification', ['title' => 'Ошибка', 'content' => 'Корзина переполнена. Очистите корзину перед удалением.', 'type' => 'danger']);
            return;
        }

        $this->dispatch('notification', ['title' => 'Удалено', 'content' => "Заметка «{$note->title}» отправлена в корзину", 'type' => 'danger']);
        $this->dispatch('noteUpdated');
        $this->dispatch('navigateTo', 'dashboard');
        // Обновляем sidebar
        $this->dispatch('refreshSidebar');
    }

    public function confirmDeletion(): void
    {
        $this->confirmingDeletion = true;
    }

    public function closeModal(): void
    {
        $this->confirmingDeletion = false;
        $this->dispatch('modalClosed');
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

    public function setContent($data): void
    {
        // Извлекаем контент из массива
        if (is_array($data) && isset($data['content'])) {
            $content = $data['content'];
        } else {
            $content = $data;
        }

        $this->content = $this->normalizeContent($content);
        $this->performSave();
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

    public function updatedTitle(): void
    {
        $this->autoSave();
    }

    public function updatedContent(): void
    {
        $this->content = $this->normalizeContent($this->content);
        $this->autoSave();
    }

    public function updatedFolderId(): void
    {
        // Этот метод теперь может не использоваться, если dropdown использует dropdownValue
        // Но оставляем для обратной совместимости
        $this->autoSave();
    }

    public function updatedDropdownValue(): void
    {
        // Сохраняем старое местоположение перед изменением
        $oldFolderId = $this->folderId;
        $oldSafeId = $this->safeId;
        $oldArchiveId = $this->archiveId;

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

        // Проверяем, изменилось ли местоположение по сравнению с оригиналом
        $locationChanged = ($this->folderId !== $this->originalFolderId) ||
                           ($this->safeId !== $this->originalSafeId) ||
                           ($this->archiveId !== $this->originalArchiveId);

        $this->autoSave($locationChanged);
    }

    public function updatedSafeId(): void
    {
        $this->autoSave();
    }

    #[On('triggerAutoSave')]
    public function autoSave(bool $locationChanged = false): void
    {
        if (!$this->noteId) {
            return;
        }

        // Проверка уникальности title - показываем предупреждение, но продолжаем сохранение
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
            // Перезагружаем из БД если кэш пуст
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

            // Обновляем оригинальные пути изображений
            $currentImagePaths = $this->extractImagePathsFromContent($this->content);
            $this->originalImagePaths = $currentImagePaths;

            // Показываем уведомление если изменилось местоположение
            if ($locationChanged) {
                $locationName = $this->getLocationName($this->cachedNote);
                $this->dispatch('notification', ['title' => 'Обновлено', 'content' => "Место хранения изменено на «{$locationName}»", 'type' => 'success']);

                // Обновляем оригинальное местоположение
                $this->originalFolderId = $this->cachedNote->folder_id;
                $this->originalSafeId = $this->cachedNote->safe_id;
                $this->originalArchiveId = $this->cachedNote->archive_id;
            }

            // Можно диспатчить событие для UI, что автосохранение прошло успешно
            // $this->dispatch('autosaveCompleted');
        } catch (\Throwable $e) {
            report($e);
            // При автосохранении не показываем ошибку пользователю
        } finally {
            $this->isSaving = false;
            // Обновляем sidebar
            $this->dispatch('refreshSidebar');
        }
    }

    private function performSave(): void
    {
        try {
            if (!$this->validateNote()) {
                return;
            }

            // Выполняем отложенное удаление изображений
            $temporaryImageService = app(TemporaryImageService::class);
            $temporaryImageService->executePendingDeletion();

            $this->updateNoteLocation();
            $this->originalImagePaths = $this->extractImagePathsFromContent($this->content);

            $this->dispatch('noteUpdated');
            $this->dispatch('navigateTo', 'dashboard');

        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('showError', 'Не удалось сохранить заметку');
        }
        // Обновляем sidebar
        $this->dispatch('refreshSidebar');
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

        // Обновляем sidebar
        $this->dispatch('refreshSidebar');
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
            $note->archive_id = null;
        }
    }

    private function updateFavorite(Note $note): void
    {
        $note->is_favorite = $this->is_favorite;
    }

    private function isSafeSelected(?int $folderId): bool
    {
        if ($folderId === null) {
            return false;
        }

        return collect($this->safes)->contains('value', 'safe_' . $folderId);
    }

    private function isArchiveSelected(?int $folderId): bool
    {
        if ($folderId === null) {
            return false;
        }

        return collect($this->archives)->contains('value', 'archive_' . $folderId);
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

    public function render()
    {
        return view('livewire.edit-note');
    }
}
