<?php

namespace App\Livewire\Layouts;

use App\Services\StateManager;
use Livewire\Component;

class AppLayout extends Component
{
    public string $section = 'dashboard';
    public ?int $folderId = null;
    public ?int $noteId = null;
    public int $componentKey = 0;


    protected $listeners = [
        'navigateTo' => 'navigateTo'
    ];

    public function navigateTo(string $section, ?int $folderId=null, ?int $noteId=null): void
    {

        StateManager::set('section', $section);
        StateManager::set('folderId', $folderId);
        StateManager::set('noteId', $noteId);

        $this->section = $section;
        $this->folderId = $folderId;
        $this->noteId = $noteId;
        $this->componentKey++;
    }

    public function render()
    {
        return view('livewire.layouts.app-layout');
    }
}