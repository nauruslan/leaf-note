<?php

namespace App\Livewire\Layouts;

use App\Services\StateManager;
use Livewire\Component;

class AppLayout extends Component
{
    public string $section = 'dashboard';
    public ?int $folderId = null;
    public string $search = '';
    public int $componentKey = 0;


    protected $listeners = [
        'stateUpdated' => 'updateState'
    ];

    public function mount(): void
    {
        // Загружаем начальное состояние из сервиса
        $this->section = StateManager::get('section') ?: 'dashboard';
        $this->folderId = StateManager::get('folderId');
        $this->search = StateManager::get('search', '');
    }

    public function updateState(string $section, ?int $folderId, string $search): void
    {
        $this->section  = $section;
        $this->folderId = $folderId;
        $this->search   = $search;
        $this->componentKey++;
    }

    public function render()
    {
        return view('livewire.layouts.app-layout');
    }
}