<?php
namespace App\Livewire;

use App\Models\Folder;
use App\Services\StateManager;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


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

        $this->loadCurrentFolder();
    }

    public function updateState(string $section, ?int $folderId, string $search): void
    {
        // $this->section  = $section;
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

        // Если передан явный ID, загружаем папку
        if ($folderId !== null) {
            $folder = Folder::where('user_id', Auth::id())->find($folderId);
        }

        \Log::info('deleteFolder called', ['folder' => $folder?->id, 'folderId' => $folderId]);

        if (!$folder) {
            \Log::warning('deleteFolder: folder is null');
            session()->flash('error', 'Папка не найдена.');
            $this->confirmingDeletion = false;
            return;
        }

        $success = $folder->moveToTrash();
        \Log::info('moveToTrash result', ['success' => $success]);

        if ($success) {
            // После удаления перенаправить на дашборд
            $this->dispatch('navigateTo', 'dashboard');
            // Уведомить навигацию об удалении папки
            $this->dispatch('folderDeleted');
            // Закрыть модал
            $this->confirmingDeletion = false;
            \Log::info('Folder moved to trash, navigating to dashboard');
        } else {
            // Ошибка, например, корзина переполнена
            session()->flash('error', 'Не удалось переместить папку в корзину. Возможно, корзина переполнена.');
            \Log::warning('moveToTrash failed, possibly trash full');
        }
    }

    public function render()
    {
        $this->loadCurrentFolder();
        return view('livewire.folder', [
            'folder' => $this->folder,
        ]);
    }
}
