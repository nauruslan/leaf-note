<?php

namespace App\Livewire\Headers;

use App\Models\Folder;
use App\Services\StateManager;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\On;

class HeaderFolder extends Component
{
    public ?int $folderId = null;
    public ?Folder $folder = null;

    public function mount(): void
    {
        $this->folderId = StateManager::get('folderId');
        $this->loadFolder();
    }

    #[On('stateUpdated')]
    public function updateState($section, $folderId, $search)
    {
        // реагируем только если раздел — folder
        if ($section !== 'folder') {
            return;
        }

        $this->folderId = $folderId;
        $this->loadFolder();
    }

    public function loadFolder(): void
    {
        $userId = Auth::id();

        if (!$userId || !$this->folderId) {
            $this->folder = null;
            return;
        }

        $this->folder = Folder::where('user_id', $userId)
            ->where('id', $this->folderId)
            ->first();
    }

    public function render()
    {
        return view('livewire.headers.folder');
    }
}