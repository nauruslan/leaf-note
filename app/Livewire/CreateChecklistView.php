<?php

namespace App\Livewire;

use Livewire\Component;

class CreateChecklistView extends Component
{
    public function saveChecklist()
    {
        // Действие сохранения списка
        $this->dispatch('checklistCreated');
    }

    public function render()
    {
        return view('livewire.create-checklist');
    }
}
