<?php
namespace App\Livewire;

use App\Models\Folder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;


class FolderView extends Component
{
    public string $section = 'folder';
    public string $search = '';
    public ?int $folderId = null;
    public bool $confirmingDeletion = false;

    protected $listeners = [
        'stateUpdated' => 'updateState',
        'noteAdded'   => 'refreshCurrentFolder',
        'noteDeleted' => 'refreshCurrentFolder',
        'closeModal'  => 'closeModal',
    ];

    #[Computed]
    public function folder(): ?Folder
    {
        $userId = Auth::id();

        return Folder::where('user_id', $userId)
            ->where('id', $this->folderId)
            ->active()
            ->first();
    }

    public function updateState(string $section, ?int $folderId): void
    {
        $this->section  = $section;
        $this->folderId = $folderId;

    }

    public function confirmDeletion(): void
    {
        $this->confirmingDeletion = true;
    }

    public function closeModal(): void
    {
        $this->confirmingDeletion = false;
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
            // После удаления перенаправить на дашборд
            $this->dispatch('navigateTo', 'dashboard');
            // Уведомить навигацию об удалении папки
            $this->dispatch('folderDeleted');
            // Закрыть модальное окно
            $this->confirmingDeletion = false;
        } else {
            // Ошибка, например, корзина переполнена
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
