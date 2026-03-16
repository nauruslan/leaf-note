<?php
namespace App\Livewire;

use App\Models\Folder;
use App\Services\StateManager;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;


class FolderView extends Component
{
    public string $section = 'folder';
    public string $search = '';
    public ?int $folderId = null;
    public bool $confirmingDeletion = false;

    protected ?Folder $folder = null;

    protected $listeners = [
        'stateUpdated' => 'updateState',
        'noteAdded'   => 'refreshCurrentFolder',
        'noteDeleted' => 'refreshCurrentFolder',
        'closeModal'  => 'closeModal',
    ];

    public function mount(): void
    {
        $this->search   = StateManager::get('search', '');
        $this->folderId = StateManager::get('folderId');
    }

    public function updateState(string $section, ?int $folderId, string $search): void
    {
        $this->section  = $section;
        $this->folderId = $folderId;
        $this->search   = $search;

        $this->loadCurrentFolder();
    }

    private function loadCurrentFolder(): void
    {
        $this->folder = $this->folderId
            ? Folder::where('user_id', Auth::id())->find($this->folderId)
            : null;
    }

    public function refreshCurrentFolder(): void
    {
        $this->loadCurrentFolder();
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
        $this->loadCurrentFolder(); // убедимся, что папка загружена
        if (!$this->folder) {
            return;
        }

        $this->dispatch('navigateTo', section: 'edit-folder', folderId: $id);
    }

    public function render()
    {
        $this->loadCurrentFolder();
        return view('livewire.folder', [
            'folder' => $this->folder,
        ]);
    }
}