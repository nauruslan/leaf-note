<?php

namespace App\Livewire;

use App\Models\Note;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ChecklistView extends Component
{
    public string $search = '';

    protected $listeners = [
        'stateUpdated' => 'updateState'
    ];

    public function mount(): void
    {
        // Инициализация для раздела списков
    }

    public function updateState(string $section, ?int $folderId, string $search): void
    {
        $this->search = $search;
    }

    public function render()
    {
        return view('livewire.checklist');
    }
}
