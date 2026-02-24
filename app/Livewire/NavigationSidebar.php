<?php

namespace App\Livewire;

use App\Models\Folder;
use App\Services\StateManager;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class NavigationSidebar extends Component
{
    public string $section = 'dashboard';
    public ?int $folderId = null;
    public $folders = [];

    protected $listeners = [
        'stateUpdated' => 'updateState'
    ];

    public function mount(): void
    {
        // Загружаем начальное состояние из сервиса
        $this->section = StateManager::get('section', 'dashboard');
        $this->folderId = StateManager::get('folderId');

        // Загружаем папки пользователя
        $userId = Auth::id();
        if (!$userId) {
            $this->folders = collect();
            return;
        }
        $this->folders = Folder::where('user_id', $userId)->orderBy('title')->get();
    }

    public function updateState($section, $folderId)
    {
        $this->section  = $section;
        $this->folderId = $folderId;
    }

    public function navigateTo(string $section, ?int $folderId = null): void
    {
        // Если нажали на тот же раздел — ничего не делаем
        if ($this->section === $section && $this->folderId === $folderId) {
            return;
        }
        $this->dispatch('navigateTo', section: $section, folderId: $folderId);
    }

    public function render()
    {
        return view('livewire.navigation-sidebar');
    }
}
