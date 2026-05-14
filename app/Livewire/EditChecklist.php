<?php

namespace App\Livewire;

use App\Dto\LocationDto;
use App\Dto\UpdateChecklistDto;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;

class EditChecklist extends BaseChecklistEditor
{

    public string $section = 'edit-checklist';
    public bool $confirmingDeletion = false;

    #[Locked]
    public ?int $noteId = null;

    public function mount(?int $noteId = null): void
    {
        if ($noteId === null) {
            $this->dispatch('notification', [
                'title' => 'Ошибка',
                'content' => 'Заметка не найдена',
                'type' => 'danger',
            ]);
            $this->dispatch('navigateTo', section: 'dashboard-section');
            return;
        }

        $this->noteId = $noteId;

        if ($this->noteId === null) {
            return;
        }

        $checklist = $this->noteService->findChecklist(Auth::id(), $this->noteId);

        if (!$checklist) {
            $this->dispatch('notification', [
                'title' => 'Ошибка',
                'content' => 'Список не найден',
                'type' => 'danger',
            ]);
            $this->dispatch('navigateTo', section: 'dashboard-section');
            return;
        }

        $this->title = $checklist->title;
        $this->folderId = $checklist->folder_id;
        $this->safeId = $checklist->safe_id;
        $this->archiveId = $checklist->archive_id;
        $this->is_favorite = (bool) $checklist->is_favorite;
        $this->content = $this->contentService->normalizeChecklistContent($checklist->content);

        $this->originalFolderId = $checklist->folder_id;
        $this->originalSafeId = $checklist->safe_id;
        $this->originalArchiveId = $checklist->archive_id;

        $this->dropdownValue = $this->locationService->buildDropdownValue(
            $this->folderId,
            $this->safeId,
            $this->archiveId,
        );

        $this->dispatch('checklistLoaded', content: $this->content);
    }

    /**
     * Получить чеклист
     */
    #[Computed]
    public function checklist(): ?Note
    {
        return $this->noteId
            ? $this->noteService->findChecklist(Auth::id(), $this->noteId)
            : null;
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
     * Подтвердить удаление
     */
    public function confirmDeletion(): void
    {
        $result = $this->noteService->deleteChecklist(Auth::id(), $this->noteId);

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

        // Получаем предыдущую секцию из StateManager
        $previousSection = \App\Services\StateManager::get('previous_section', 'dashboard-section');
        $previousFolderId = \App\Services\StateManager::get('previous_folderId', null);

        // Если предыдущая секция - это секция редактирования, то переходим на dashboard
        if (in_array($previousSection, ['edit-note', 'edit-checklist', 'edit-folder'])) {
            $previousSection = 'dashboard-section';
            $previousFolderId = null;
        }

        $this->dispatch('navigateTo', section: $previousSection, folderId: $previousFolderId);
        $this->dispatch('refreshSidebar');
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
                'content' => 'Список с таким названием уже есть. Чтобы избежать путаницы измените название.',
                'type' => 'warning',
            ]);
        }

        if (!$this->validateAndSave()) {
            return;
        }

        $this->isSaving = true;

        try {
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
        } catch (\Throwable $e) {
            report($e);
        } finally {
            $this->isSaving = false;
            $this->dispatch('refreshSidebar');
        }
    }

    /**
     * Обработать обновление чеклиста
     */
    #[On('checklistUpdated')]
    public function onChecklistUpdated(): void
    {
        $this->dispatch('navigateTo', section: 'dashboard-section');
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.edit-checklist');
    }
}
