<?php

namespace App\Livewire\Layouts;

use App\Services\StateManager;
use App\Services\TemporaryImageService;
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

        // Если покидаем контекст сейфа (переход из safe-секции в другую секцию),
        // Safe-контекст: safe, edit-note, edit-checklist, create-note, create-checklist
        $safeContextSections = ['safe', 'edit-note', 'edit-checklist', 'create-note', 'create-checklist'];
        $leavingSafeContext = in_array($this->section, $safeContextSections) && !in_array($section, $safeContextSections);
        if ($leavingSafeContext) {
            StateManager::remove('safe_unlocked');
        }

        // Если покидаем страницу создания заметки/чеклиста, удаляем несохраненные временные изображения
        $createSections = ['create-note', 'create-checklist'];
        $leavingCreateSection = in_array($this->section, $createSections) && !in_array($section, $createSections);
        if ($leavingCreateSection) {
            $temporaryImageService = app(TemporaryImageService::class);
            $temporaryImageService->deleteUnsavedImages();
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