<?php

namespace App\Livewire\Headers;

use App\Services\StateManager;
use Livewire\Component;

class HeaderArchive extends Component
{
    public string $section = 'archive';
    public ?int $folderId = null;

    protected $listeners = [
        'stateUpdated' => 'updateState'
    ];

    public function mount(): void
    {
        // Загружаем начальное состояние из сервиса
        $this->section = StateManager::get('section', 'archive');
        $this->folderId = StateManager::get('folderId');
    }

    public function updateState($section, $folderId, $search)
    {
        // Компонент должен реагировать только на свой раздел
        if ($section === 'archive') {
            $this->section  = $section;
            $this->folderId = $folderId;
        }
    }

    public function render()
    {
        return view('livewire.headers.archive');
    }
}