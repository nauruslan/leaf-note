<?php

namespace App\Livewire;

use App\Dto\LocationDto;
use App\Dto\UpdateNoteDto;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;

class EditNote extends BaseNoteEditor
{
    public string $section = 'edit-note';
    public bool $confirmingDeletion = false;
    public bool $isLoaded = false;

    #[Locked]
    public ?int $noteId = null;

    public function mount(?int $noteId = null, ?int $folderId = null): void
    {
        $this->noteId = $noteId;
        $this->folderId = $folderId;

        if ($this->noteId) {
            $this->loadNote();
        }
    }

    /**
     * Загрузить заметку
     */
    public function loadNote(): void
    {
        if (!$this->noteId) {
            return;
        }

        $note = $this->noteService->findNote(Auth::id(), $this->noteId);

        if (!$note) {
            return;
        }

        $this->noteId = $note->id;
        $this->title = $note->title;
        $this->folderId = $note->folder_id;
        $this->safeId = $note->safe_id;
        $this->archiveId = $note->archive_id;
        $this->is_favorite = (bool) $note->is_favorite;
        $this->content = $note->content;
        $this->isLoaded = true;

        // Сохраняем оригинальное местоположение для отслеживания изменений
        $this->originalFolderId = $note->folder_id;
        $this->originalSafeId = $note->safe_id;
        $this->originalArchiveId = $note->archive_id;

        // Инициализируем dropdownValue
        $this->dropdownValue = $this->locationService->buildDropdownValue(
            $this->folderId,
            $this->safeId,
            $this->archiveId,
        );

        $this->dispatch('noteLoaded',
            content: $this->content,
            originalImagePaths: $this->extractImagePathsFromContent($note->content)
        );
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

    /**
     * Отмена редактирования
     */
    public function cancel(): void
    {
        // Очищаем отложенные удаления изображений
        $this->temporaryImageService->clearPendingDeletion();

        $this->js('localStorage.clear()');
        $this->dispatch('restoreNoteOriginalState');
        $this->dispatch('navigateTo', section: 'dashboard-section');
    }

    /**
     * Подтвердить удаление
     */
    public function confirmDeletion(): void
    {
        $result = $this->noteService->deleteNote(Auth::id(), $this->noteId);

        if (!$result['success']) {
            $this->dispatch('notification', [
                'title' => 'Ошибка',
                'content' => $result['message'],
                'type' => 'danger',
            ]);
            return;
        }

        $this->dispatch('notification', [
            'title' => 'Удалено',
            'content' => $result['message'],
            'type' => 'danger',
        ]);
        $this->dispatch('navigateTo', section: 'dashboard-section');
        $this->dispatch('refreshSidebar');
    }

    /**
     * Открыть модальное окно удаления
     */
    public function openDeleteModal(): void
    {
        $this->confirmingDeletion = true;
    }

    /**
     * Закрыть модальное окно
     */
    public function closeModal(): void
    {
        $this->confirmingDeletion = false;
        $this->dispatch('modalClosed');
    }

    /**
     * Автосохранение
     */
    #[Locked]
    public function autoSave(bool $locationChanged = false): void
    {
        if (!$this->noteId) {
            return;
        }

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
            // Выполняем отложенное удаление изображений
            $this->executePendingImageDeletion();

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
        } catch (\Throwable $e) {
            report($e);
        } finally {
            $this->isSaving = false;
            $this->dispatch('refreshSidebar');
        }
    }

    /**
     * Обработать обновление заметки
     */
    #[On('noteUpdated')]
    public function onNoteUpdated(): void
    {
        $this->dispatch('navigateTo', section: 'dashboard-section');
    }

    /**
     * Обработать навигацию
     */
    #[On('navigateTo')]
    public function handleNavigateTo(string $section, ?int $folderId = null): void
    {
        if ($section === 'edit-note' && $folderId) {
            $this->openNote($folderId);
        }
    }

    /**
     * Открыть заметку
     */
    #[On('openNote')]
    public function openNote($noteId): void
    {
        $this->noteId = $noteId;
        $this->loadNote();
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.edit-note');
    }
}