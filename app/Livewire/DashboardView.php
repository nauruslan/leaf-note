<?php

namespace App\Livewire;

use App\Services\StateManager;
use Livewire\Component;

class DashboardView extends Component
{
    public string $search = '';

    protected $listeners = [
        'stateUpdated' => 'updateState'
    ];

    public function mount(): void
    {
        $this->search = StateManager::get('search', '');
    }

    public function updateState(string $section, ?int $folderId, string $search): void
    {
        $this->search = $search;
    }

    public function createNote()
    {
        // Действие создания заметки
        $this->dispatch('openModal', 'create-note');
    }

    public function createChecklist()
    {
        // Действие создания списка
        $this->dispatch('openModal', 'create-checklist');
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
