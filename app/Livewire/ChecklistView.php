<?php

namespace App\Livewire;

use App\Services\StateManager;
use Livewire\Component;

class ChecklistView extends Component
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

    public function saveChecklist()
    {
        // Логика сохранения списка
        $this->dispatch('notify', ['message' => 'Список сохранён', 'type' => 'success']);
    }

    public function deleteChecklist($id)
    {
        // Логика удаления списка
        $this->dispatch('notify', ['message' => 'Список удалён', 'type' => 'warning']);
    }

    public function toggleComplete($id)
    {
        // Логика переключения выполнения
        $this->dispatch('notify', ['message' => 'Статус обновлён', 'type' => 'info']);
    }

    public function render()
    {
        return view('livewire.checklist');
    }
}