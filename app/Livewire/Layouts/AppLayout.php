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
    ];

    public function navigateTo(string $section, ?int $folderId=null, ?int $noteId=null): void
    {
        // Сохраняем текущую секцию как предыдущую перед переходом
        StateManager::set('previous_section', $this->section);
        StateManager::set('previous_folderId', $this->folderId);
        StateManager::set('previous_noteId', $this->noteId);

        // Если покидаем контекст сейфа (переход из safe-секции в не-safe-секцию),
        // сбрасываем флаг разблокировки, чтобы требовать пароль при следующем входе в сейф.
        // Safe-контекст: safe, edit-note, edit-checklist, create-note, create-checklist
        $safeContextSections = ['safe', 'edit-note', 'edit-checklist', 'create-note', 'create-checklist'];
        $leavingSafeContext = in_array($this->section, $safeContextSections) && !in_array($section, $safeContextSections);
        if ($leavingSafeContext) {
            StateManager::remove('safe_unlocked');
        }

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