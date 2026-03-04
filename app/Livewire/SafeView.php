<?php

namespace App\Livewire;

use App\Services\StateManager;
use Livewire\Component;

class SafeView extends Component
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

    public function createSafeNote()
    {
        // Действие создания защищённой заметки
        $this->dispatch('openModal', 'create-safe-note');
    }

    public function render()
    {
        return view('livewire.safe');
    }
}