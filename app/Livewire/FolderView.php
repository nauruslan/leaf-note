<?php

namespace App\Livewire;

use App\Services\StateManager;
use Livewire\Component;
use App\Models\Folder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

class FolderView extends Component
{
    public string $section = 'folder';
    public string $search = '';
    public ?int $folderId = null;

    public ?Folder $folder = null;

    protected $listeners = [
        'stateUpdated' => 'updateState'
    ];

    public function mount(): void
    {
        $this->section = StateManager::get('section', 'folder');
        $this->search = StateManager::get('search', '');
        $this->folderId = StateManager::get('folderId');
        $this->loadFolder();
    }

    #[On('stateUpdated')]
    public function updateState($section, $folderId, $search)
    {
        $this->section = $section;
        $this->folderId = $folderId;
        $this->search = $search;
        $this->loadFolder();
    }

    public function loadFolder()
    {
        if ($this->folderId) {
            $this->folder = Folder::where('user_id', Auth::id())
                ->where('id', $this->folderId)
                ->first();
        }
    }


    public function render()
    {
        return view('livewire.folder');
    }

}