<?php

namespace App\Livewire\Layouts;

use App\Services\DemoUserService;
use App\Services\StateManager;
use App\Services\TemporaryImageService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AppLayout extends Component
{
    public string $section = 'dashboard';
    public ?int $folderId = null;
    public ?int $noteId = null;
    public int $componentKey = 0;
    public bool $showDemoModal = false;
    public string $demoExpirationTime = '';


    public function mount(): void
    {
        $this->section = StateManager::get('section', 'dashboard');
        $this->folderId = StateManager::get('folderId', null);
        $this->noteId = StateManager::get('noteId', null);

        $this->initDemoModal();

        // Проверяем, был ли сброшен пароль сейфа через email
        if (session()->has('safe_password_reset')) {
            session()->forget('safe_password_reset');
            $this->dispatch('notification', ['title' => 'Внимание', 'content' => 'Был сброшен пароль от сейфа', 'type' => 'warning']);
        }
    }

    /**
     * Инициализация модального окна для демо-пользователей.
     * Показывает информацию о сроке действия демо-аккаунта.
     */
    protected function initDemoModal(): void
    {
        $user = Auth::user();

        if ($user && $user->isDemoUser()) {
            // Проверяем, нужно ли показать модальное окно (только при первом входе в сессию)
            if (!session()->has('demo_modal_shown')) {
                $this->showDemoModal = true;
                session()->put('demo_modal_shown', true);
            }

            // Вычисляем время истечения демо-аккаунта
            $expirationDate = $user->created_at->addMinutes(DemoUserService::DEMO_LIFETIME_MINUTES);
            $this->demoExpirationTime = $expirationDate->format('d.m.Y H:i');
        }
    }

    // Закрыть модальное окно демо-информации.
    public function closeDemoModal(): void
    {
        $this->showDemoModal = false;
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

        // Если покидаем страницу создания заметки, удаляем несохраненные временные изображения
        // (только create-note)
        if ($this->section === 'create-note' && $section !== 'create-note') {
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

        $this->dispatch('stateUpdated', section:$section, folderId:$folderId);
    }

    public function render()
    {
        return view('livewire.layouts.app-layout');
    }
}
