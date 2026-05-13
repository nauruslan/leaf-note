<?php

namespace App\Livewire;

use App\Dto\CreateNoteDto;
use App\Dto\LocationDto;
use App\Dto\UpdateNoteDto;
use App\Models\Note;
use App\Services\StateManager;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;

class CreateNote extends BaseNoteEditor
{
    public string $heading = 'Создать заметку';
    public string $section = 'create-note';
    public bool $isFirstSave = true;

    // Сохраняем оригинальные пути изображений для отслеживания изменений
    protected array $originalImagePaths = [];

    public function mount(): void
    {
        // Инициализация контента
        $this->content = $this->contentService->normalizeNoteContent('');

        // Очищаем список временных изображений при входе на страницу создания заметки
        $this->clearTemporaryImages();

        // Обработка предустановок из StateManager
        $this->handlePresetFromStateManager();
    }

    /**
     * Обработка предустановок из StateManager
     */
    protected function handlePresetFromStateManager(): void
    {
        $presetIsFavorite = StateManager::get('preset_is_favorite', false);
        if ($presetIsFavorite) {
            $this->is_favorite = true;
            StateManager::remove('preset_is_favorite');
        }

        $presetSafeId = StateManager::get('preset_safe_id');
        if ($presetSafeId) {
            $this->safeId = $presetSafeId;
            $this->dropdownValue = 'safe_' . $presetSafeId;
            $this->originalFolderId = null;
            $this->originalSafeId = $presetSafeId;
            $this->originalArchiveId = null;
            StateManager::remove('preset_safe_id');
            return;
        }

        $presetArchiveId = StateManager::get('preset_archive_id');
        if ($presetArchiveId) {
            $this->archiveId = $presetArchiveId;
            $this->dropdownValue = 'archive_' . $presetArchiveId;
            $this->originalFolderId = null;
            $this->originalSafeId = null;
            $this->originalArchiveId = $presetArchiveId;
            StateManager::remove('preset_archive_id');
            return;
        }

        $presetFolderId = StateManager::get('preset_folder_id');
        if ($presetFolderId) {
            $this->folderId = $presetFolderId;
            $this->dropdownValue = (string) $presetFolderId;
            $this->originalFolderId = $presetFolderId;
            $this->originalSafeId = null;
            $this->originalArchiveId = null;
            StateManager::remove('preset_folder_id');
        }
    }


    /**
     * Обработать событие обновления контента для отслеживания удаленных изображений
     */
    #[On('editorContent')]
    public function onEditorContent($content): void
    {
        $this->content = $content;

        // Если есть оригинальные пути, проверяем удаленные изображения
        if (!empty($this->originalImagePaths)) {
            $currentPaths = $this->extractImagePathsFromContent($content);
            $this->deleteRemovedImages($this->originalImagePaths, $currentPaths, $this->noteId);
        }
    }

    /**
     * Обработка готового контента
     */
    #[On('noteContentReady')]
    public function handleContentReady($data): void
    {
        // Извлекаем контент из массива, если он обернут
        if (is_array($data) && isset($data['content'])) {
            $content = $data['content'];
        } else {
            $content = $data;
        }

        $this->content = $content;
        $this->autoSave();
    }

    /**
     * Автосохранение
     */
    #[Locked]
    public function autoSave(bool $locationChanged = false): void
    {
        // Проверка условий для сохранения
        if (!$this->canSave()) {
            return;
        }

        // Проверка уникальности названия
        if ($this->noteService->isTitleExists(Auth::id(), trim($this->title), $this->noteId)) {
            $this->dispatch('notification', [
                'title' => 'Внимание',
                'content' => 'Заметка с таким названием уже есть. Чтобы избежать путаницы измените название.',
                'type' => 'warning',
            ]);
        }

        if (!$this->validateAndSave()) {
            return;
        }

        $this->isSaving = true;

        try {
            if ($this->noteId) {
                $this->updateExistingNote($locationChanged);
            } else {
                $this->createNewNote();
            }
        } catch (\Throwable $e) {
            report($e);
        } finally {
            $this->isSaving = false;
            $this->dispatch('refreshSidebar');
        }
    }

    /**
     * Проверить, можно ли сохранить
     */
    protected function canSave(): bool
    {
        return ($this->folderId !== null || $this->safeId !== null || $this->archiveId !== null)
            && trim($this->title) !== '';
    }

    /**
     * Создать новую заметку
     */
    protected function createNewNote(): void
    {
        $dto = new CreateNoteDto(
            userId: Auth::id(),
            title: trim($this->title),
            content: $this->contentService->normalizeNoteContent($this->content),
            isFavorite: $this->is_favorite,
            location: new LocationDto(
                folderId: $this->folderId,
                safeId: $this->safeId,
                archiveId: $this->archiveId,
            ),
        );

        $note = $this->noteService->createNote($dto);

        $this->noteId = $note->id;
        $this->originalFolderId = $note->folder_id;
        $this->originalSafeId = $note->safe_id;
        $this->originalArchiveId = $note->archive_id;

        // Очищаем временные изображения при успешном сохранении
        $this->clearTemporaryImages();

        // Сохраняем оригинальные пути после первого сохранения
        $this->originalImagePaths = $this->extractImagePathsFromContent($this->content);

        if ($this->isFirstSave) {
            $locationName = $this->locationService->getLocationName($note);
            $this->dispatch('notification', [
                'title' => 'Сохранено',
                'content' => "Заметка сохранена в «{$locationName}»",
                'type' => 'success',
            ]);
            $this->isFirstSave = false;
        }
    }

    /**
     * Обновить существующую заметку
     */
    protected function updateExistingNote(bool $locationChanged): void
    {
        // Получаем текущие пути изображений из контента
        $currentImagePaths = $this->extractImagePathsFromContent($this->content);

        $dto = new UpdateNoteDto(
            userId: Auth::id(),
            noteId: $this->noteId,
            title: trim($this->title),
            content: $this->content,
            isFavorite: $this->is_favorite,
            location: new LocationDto(
                folderId: $this->folderId,
                safeId: $this->safeId,
                archiveId: $this->archiveId,
            ),
        );

        $note = $this->noteService->updateNote($dto);

        if ($locationChanged) {
            $this->dispatchLocationChangedNotification($note);
            $this->originalFolderId = $note->folder_id;
            $this->originalSafeId = $note->safe_id;
            $this->originalArchiveId = $note->archive_id;
        }

        // Очищаем временные изображения при успешном обновлении
        $this->clearTemporaryImages();

        // Обновляем оригинальные пути после успешного сохранения
        $this->originalImagePaths = $currentImagePaths;
    }

    /**
     * Получить заметку
     */
    #[Computed]
    public function note(): ?Note
    {
        return $this->noteId
            ? $this->noteService->findNote(Auth::id(), $this->noteId)
            : null;
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.create-note');
    }
}
