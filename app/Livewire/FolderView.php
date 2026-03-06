<?php
namespace App\Livewire;

use App\Models\Folder;
use App\Services\StateManager;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;


class FolderView extends Component
{
    public string $section = 'folder';
    public string $search = '';
    public ?int $folderId = null;

    protected ?Folder $folder = null;

    protected $listeners = [
        'stateUpdated' => 'updateState',
        'noteAdded'   => 'refreshCurrentFolder',
        'noteDeleted' => 'refreshCurrentFolder',
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

    public function render()
    {
        return view('livewire.folder', [
            'folder' => $this->folder,
        ]);
    }
}
