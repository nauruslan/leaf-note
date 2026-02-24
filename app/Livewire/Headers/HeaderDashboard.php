<?php

namespace App\Livewire\Headers;

use App\Services\StateManager;
use Livewire\Component;

class HeaderDashboard extends Component
{
    public string $section = 'dashboard';
    public ?int $folderId = null;

    protected $listeners = [
        'stateUpdated' => 'updateState'
    ];

    public function mount(): void
    {
        // Загружаем начальное состояние из сервиса
        $this->section = StateManager::get('section', 'dashboard');
        $this->folderId = StateManager::get('folderId');
    }

    public function updateState($section, $folderId)
    {
        $this->section  = $section;
        $this->folderId = $folderId;
    }

    public function render()
    {
        return view('livewire.headers.dashboard');
    }
}