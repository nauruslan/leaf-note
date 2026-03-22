<?php

namespace App\Livewire;

use Livewire\Component;

class ChecklistView extends Component
{
    public string $search = '';

    protected $listeners = [
        'stateUpdated' => 'updateState'
    ];

    public function render()
    {
        return view('livewire.checklist');
    }
}