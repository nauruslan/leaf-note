<?php

namespace App\Livewire;

use App\Dto\CreateChecklistDto;
use App\Dto\LocationDto;
use App\Dto\UpdateChecklistDto;
use App\Services\StateManager;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;

class CreateChecklist extends BaseChecklistEditor
{

    public string $heading = 'Создать список';
    public string $section = 'create-checklist';
    public bool $isFirstSave = true;

    public function mount(): void
    {
        // Инициализация контента
        $this->content = $this->contentService->normalizeChecklistContent('');

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
            $this->originalSafeId = $presetSafeId;
            StateManager::remove('preset_safe_id');
            return;
        }

        $presetArchiveId = StateManager::get('preset_archive_id');
        if ($presetArchiveId) {
            $this->archiveId = $presetArchiveId;
            $this->dropdownValue = 'archive_' . $presetArchiveId;
            $this->originalArchiveId = $presetArchiveId;
            StateManager::remove('preset_archive_id');
            return;
        }

        $presetFolderId = StateManager::get('preset_folder_id');
        if ($presetFolderId) {
            $this->folderId = $presetFolderId;
            $this->dropdownValue = (string) $presetFolderId;
            $this->originalFolderId = $presetFolderId;
            StateManager::remove('preset_folder_id');
        }
    }

    /**
     * Обработка готового контента
     */
    #[On('checklistContentReady')]
    public function handleContentReady(string $content): void
    {
        $this->content = $content;
        $this->autoSave();
    }

    /**
     * Обновление folderId
     */
    public function updatedFolderId(): void
    {
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
                'content' => 'Список с таким названием уже есть. Чтобы избежать путаницы измените название.',
                'type' => 'warning',
            ]);
        }

        if (!$this->validateAndSave()) {
            return;
        }

        $this->isSaving = true;

        try {
            if ($this->noteId) {
                $this->updateExistingChecklist($locationChanged);
            } else {
                $this->createNewChecklist();
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
     * Создать новый чеклист
     */
    protected function createNewChecklist(): void
    {
        $dto = new CreateChecklistDto(
            userId: Auth::id(),
            title: trim($this->title),
            content: $this->contentService->normalizeChecklistContent($this->content),
            isFavorite: $this->is_favorite,
            location: new LocationDto(
                folderId: $this->folderId,
                safeId: $this->safeId,
                archiveId: $this->archiveId,
            ),
        );

        $note = $this->noteService->createChecklist($dto);

        $this->noteId = $note->id;
        $this->originalFolderId = $note->folder_id;
        $this->originalSafeId = $note->safe_id;
        $this->originalArchiveId = $note->archive_id;

        if ($this->isFirstSave) {
            $locationName = $this->locationService->getLocationName($note);
            $this->dispatch('notification', [
                'title' => 'Сохранено',
                'content' => "Список сохранён в «{$locationName}»",
                'type' => 'success',
            ]);
            $this->isFirstSave = false;
        }
    }

    /**
     * Обновить существующий чеклист
     */
    protected function updateExistingChecklist(bool $locationChanged): void
    {
        $dto = new UpdateChecklistDto(
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

        $note = $this->noteService->updateChecklist($dto);

        if ($locationChanged) {
            $this->dispatchLocationChangedNotification($note);
            $this->originalFolderId = $note->folder_id;
            $this->originalSafeId = $note->safe_id;
            $this->originalArchiveId = $note->archive_id;
        }
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.create-checklist');
    }
}