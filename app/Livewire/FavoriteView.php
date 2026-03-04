<?php

namespace App\Livewire;

use App\Services\StateManager;
use Livewire\Component;

class FavoriteView extends Component
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


    public function render()
    {
        return view('livewire.favorite');
    }
}
