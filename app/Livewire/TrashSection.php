<?php

namespace App\Livewire;

use App\Livewire\Traits\WithComponentPagination;
use App\Livewire\Traits\WithFiltering;
use App\Livewire\Traits\WithSearch;
use App\Livewire\Traits\WithTrashModals;
use App\Services\TrashService;
use App\Services\TrashQueryService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class TrashSection extends Component
{
    use WithComponentPagination;
    use WithSearch;
    use WithFiltering;
    use WithTrashModals;

    public string $section = 'trash-section';
    public string $heading = 'Корзина';
    public string $subheading = '';

    // Внедряемые сервисы
    protected TrashService $trashService;
    protected TrashQueryService $trashQueryService;

    public function boot(
        TrashService $trashService,
        TrashQueryService $trashQueryService,
    ): void {
        $this->trashService = $trashService;
        $this->trashQueryService = $trashQueryService;
    }

    public function mount(): void
    {
        $this->sort = 'deleted';
        $this->setSubheading();
    }

    /**
     * Установить subheading в зависимости от настроек автоочистки
     */
    private function setSubheading(): void
    {
        $autoDeleteDays = $this->trashService->getAutoDeleteDays(Auth::id());
        if ($autoDeleteDays !== 'disabled') {
            $this->subheading = 'Включена автоочистка корзины';
        }
    }

    /**
     * Подтвердить восстановление
     */
    #[Locked]
    public function confirmRestore(): void
    {
        if ($this->pendingRestoreId === null || $this->pendingRestoreType === null) {
            $this->closeModal();
            return;
        }

        $result = $this->pendingRestoreType === 'folder'
            ? $this->trashService->restoreFolder(Auth::id(), $this->pendingRestoreId)
            : $this->trashService->restoreNote(Auth::id(), $this->pendingRestoreId);

        if ($result->success) {
            $this->dispatch('notification', [
                'title' => 'Информация',
                'content' => $result->message,
                'type' => 'info',
            ]);
            $this->dispatch('refreshSidebar');
        }

        $this->closeModal();
    }

    /**
     * Подтвердить удаление
     */
    #[Locked]
    public function confirmDelete(): void
    {
        if ($this->pendingDeleteId === null || $this->pendingDeleteType === null) {
            $this->closeModal();
            return;
        }

        $result = $this->pendingDeleteType === 'folder'
            ? $this->trashService->deleteFolder(Auth::id(), $this->pendingDeleteId)
            : $this->trashService->deleteNote(Auth::id(), $this->pendingDeleteId);

        if ($result->success) {
            $this->dispatch('notification', [
                'title' => 'Информация',
                'content' => $result->message,
                'type' => 'info',
            ]);
            $this->dispatch('refreshSidebar');
        }

        $this->closeModal();
    }

    /**
     * Очистить корзину
     */
    #[Locked]
    public function emptyTrash(): void
    {
        $result = $this->trashService->emptyTrash(Auth::id());

        if ($result->success) {
            $this->dispatch('notification', [
                'title' => 'Информация',
                'content' => $result->message,
                'type' => 'info',
            ]);
            $this->dispatch('refreshSidebar');
        }

        $this->closeEmptyTrashModal();
    }

    /**
     * Восстановить всё
     */
    #[Locked]
    public function restoreAll(): void
    {
        $result = $this->trashService->restoreAll(Auth::id());

        if ($result->success) {
            $this->dispatch('notification', [
                'title' => 'Информация',
                'content' => $result->message,
                'type' => 'info',
            ]);
            $this->dispatch('refreshSidebar');
        }

        $this->closeRestoreAllModal();
    }

    /**
     * Удалённые заметки
     */
    #[Computed]
    public function trashedNotes(): LengthAwarePaginator
    {
        return $this->trashQueryService->getTrashedNotes(
            userId: Auth::id(),
            search: $this->search,
            filter: $this->filter,
            sort: $this->sort,
            page: $this->page,
            perPage: $this->perPage,
        );
    }

    /**
     * Удалённые папки
     */
    #[Computed]
    public function trashedFolders(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->trashQueryService->getTrashedFolders(
            userId: Auth::id(),
            search: $this->search,
            sort: $this->sort,
        );
    }

    /**
     * Общее количество элементов в корзине
     */
    #[Computed]
    public function totalCount(): int
    {
        return $this->trashService->getTotalCount(Auth::id());
    }

    public function render()
    {
        return view('livewire.trash');
    }
}