<?php

namespace App\Livewire\Layouts;

use App\Services\StateManager;
use Livewire\Component;

class AppLayout extends Component
{
    public string $section = 'dashboard';
    public ?int $folderId = null;
    public int $componentKey = 0;


    protected $listeners = [
        'navigateTo' => 'handleNavigateTo'
    ];

    public function handleNavigateTo(string $section, ?int $folderId=null): void
    {

        StateManager::set('section', $section);
        StateManager::set('folderId', $folderId);

        $this->section = $section;
        $this->folderId = $folderId;
        $this->componentKey++;
    }

    public function render()
    {
        return view('livewire.layouts.app-layout');
    }
}
