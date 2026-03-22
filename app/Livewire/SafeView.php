<?php

namespace App\Livewire;

use Livewire\Component;

class SafeView extends Component
{
    public string $search = '';

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