<?php

namespace App\Livewire;

use App\Models\Folder;
use App\Services\FolderService;
use App\Services\NoteQueryService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;

class FolderSection extends Base
{
    public string $section = 'folder-section';
    public bool $confirmingDeletion = false;

    #[On('closeModal')]
    public function closeModal(): void
    {
        $this->confirmingDeletion = false;
        $this->dispatch('modalClosed');
    }

    public function mount(?int $folderId = null): void
    {
        $this->folderId = $folderId;
    }

    protected function getBaseConditions(): array
    {
        return ['folder_id' => $this->folderId];
    }

    protected function getTotalCount(): int
    {
        if (!$this->folderId) {
            return 0;
        }

        return app(NoteQueryService::class)->getFolderNotesCount(Auth::id(), $this->folderId);
    }

    #[Computed]
    public function totalFolderNotesCount(): int
    {
        return $this->getTotalCount();
    }

    #[Computed]
    public function folder(): ?Folder
    {
        if (!$this->folderId) {
            return null;
        }

        return app(FolderService::class)->getFolder(Auth::id(), $this->folderId);
    }

    /**
     * Переопределяем notes() для обработки случая без folderId.
     */
    #[Computed]
    public function notes(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        if (!$this->folderId) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage);
        }

        return parent::notes();
    }

    public function confirmDeletion(): void
    {
        $this->confirmingDeletion = true;
    }

    public function deleteFolder(?int $folderId = null): void
    {
        $targetFolderId = $folderId ?? $this->folderId;

        if (!$targetFolderId) {
            $this->confirmingDeletion = false;
            return;
        }

        $result = app(FolderService::class)->deleteFolder(Auth::id(), $targetFolderId);

        if ($result['success']) {
            $this->dispatch('notification', ['title' => 'Удалено', 'content' => $result['message'], 'type' => 'danger']);
            // После удаления перенаправить на дашборд
            $this->dispatch('navigateTo', section: 'dashboard-section');
            // Обновляем sidebar (получит новое значение section через проп от AppLayout)
            $this->dispatch('refreshSidebar');
            // Закрыть модальное окно
            $this->confirmingDeletion = false;
        } else {
            // Корзина переполнена или папка не найдена
            $this->dispatch('notification', ['title' => 'Ошибка', 'content' => $result['message'], 'type' => 'danger']);
            $this->confirmingDeletion = false;
        }
    }

    public function openEditFolder($id): void
    {
        if (!$this->folder) {
            return;
        }

        $this->dispatch('navigateTo', section: 'edit-folder', folderId: $id, noteId: null);
    }

    public function render()
    {
        return view('livewire.folder');
    }
}