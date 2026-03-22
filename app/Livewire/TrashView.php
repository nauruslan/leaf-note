<?php

namespace App\Livewire;

use App\Services\StateManager;
use Livewire\Component;

class TrashView extends Component
{

    public string $search = '';

    protected $listeners = [
        'stateUpdated' => 'updateState'
    ];


    public function render()
    {
        return view('livewire.trash');
    }
}
