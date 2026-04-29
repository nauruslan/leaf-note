<?php

namespace App\Livewire;

use App\Models\Folder;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

class FolderView extends BaseView
{
    public string $section = 'folder';
    public bool $confirmingDeletion = false;

    protected function getListeners()
    {
        return array_merge(parent::getListeners(), [
            'closeModal' => 'closeModal',
        ]);
    }

    public function mount(?int $folderId = null): void
    {
        $this->folderId = $folderId;
        $this->heading = 'Папка';
        $this->subheading = $this->folder?->title ?? '';
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

        return Note::forUser(Auth::id())
            ->where('folder_id', $this->folderId)
            ->count();
    }

    #[Computed]
    public function folder(): ?Folder
    {
        if (!$this->folderId) {
            return null;
        }

        return Folder::where('user_id', Auth::id())
            ->where('id', $this->folderId)
            ->active()
            ->first();
    }

    /**
     * Общее количество заметок в текущей папке.
     */
    #[Computed(cache: true, seconds: 10*60)]
    public function totalFolderNotesCount(): int
    {
        return $this->getTotalCount();
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

    public function closeModal(): void
    {
        $this->confirmingDeletion = false;
        $this->dispatch('modalClosed');
    }

    public function deleteFolder(?int $folderId = null): void
    {
        $folder = $this->folder;

        if ($folderId !== null) {
            $folder = Folder::where('user_id', Auth::id())->find($folderId);
        }

        if (!$folder) {
            $this->confirmingDeletion = false;
            return;
        }

        $success = $folder->moveToTrash();

        if ($success) {
            $this->dispatch('notification', ['title' => 'Удалено', 'content' => "Папка «{$folder->title}» отправлена в корзину", 'type' => 'danger']);
            // После удаления перенаправить на дашборд
            $this->dispatch('navigateTo', section: 'dashboard');
            // Обновляем sidebar (получит новое значение section через проп от AppLayout)
            $this->dispatch('refreshSidebar');
            // Закрыть модальное окно
            $this->confirmingDeletion = false;
        } else {
            // Корзина переполнена
            $this->dispatch('notification', ['title' => 'Ошибка', 'content' => 'Корзина переполнена. Очистите корзину перед удалением.', 'type' => 'danger']);
            $this->confirmingDeletion = false;
        }
    }

    public function openEditFolder($id): void
    {
        if (!$this->folder) {
            return;
        }

        $this->dispatch('navigateTo', section: 'edit-folder', folderId: $id);
    }

    public function render()
    {
        return view('livewire.folder');
    }
}