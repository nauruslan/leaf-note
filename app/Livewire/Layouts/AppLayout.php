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


    public function mount(): void
    {
        $this->section = StateManager::get('section', 'dashboard');
        $this->folderId = StateManager::get('folderId', null);
        $this->noteId = StateManager::get('noteId', null);
    }

    protected $listeners = [
        'navigateTo' => 'navigateTo',
        'notification' => 'handleNotification'
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

    public function handleNotification(string $title, string $content, string $type = 'info'): void
    {
        $this->dispatch('show-notification', title: $title, content: $content, type: $type);
    }

    public function render()
    {
        return view('livewire.layouts.app-layout');
    }
}