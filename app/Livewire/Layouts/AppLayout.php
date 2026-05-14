<?php

namespace App\Livewire\Layouts;

use App\Services\DemoUserService;
use App\Services\StateManager;
use App\Services\TemporaryImageService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AppLayout extends Component
{
    public string $section = 'dashboard-section';
    public ?int $folderId = null;
    public ?int $noteId = null;
    public int $componentKey = 0;
    public bool $showDemoModal = false;
    public string $demoExpirationTime = '';
    public bool $isLoading = false;
    public ?string $loadingSection = null;


    public function mount(): void
    {
        $this->section = StateManager::get('section', 'dashboard-section');
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
        'startLoading' => 'startLoading',
        'finishLoading' => 'finishLoading',
    ];

    public ?int $loadingNoteId = null;

    public function startLoading(string $section, ?int $folderId = null, ?int $noteId = null): void
    {
        $this->isLoading = true;
        $this->loadingSection = $section;
        $this->loadingNoteId = $noteId;
    }

    public function finishLoading(): void
    {
        $this->isLoading = false;
        $this->loadingSection = null;
        $this->loadingNoteId = null;
    }

    public function navigateTo(string $section, ?int $folderId=null, ?int $noteId=null): void
    {
       // Сохраняем текущую секцию как предыдущую перед переходом
        // Но не перезаписываем, если уходим с страницы создания (текущая секция - create-секция)
        // потому что previous_section уже был правильно сохранен при переходе к create
        $createSections = ['create-note', 'create-checklist', 'create-folder'];
        if (!in_array($this->section, $createSections)) {
            StateManager::set('previous_section', $this->section);
            StateManager::set('previous_folderId', $this->folderId);
            StateManager::set('previous_noteId', $this->noteId);
        }

        // Если переходим на страницу создания, всегда сохраняем текущую секцию как предыдущую
        if (in_array($section, $createSections)) {
            StateManager::set('previous_section', $this->section);
            StateManager::set('previous_folderId', $this->folderId);
            StateManager::set('previous_noteId', $this->noteId);
        }

        // Если покидаем контекст сейфа (переход из safe-секции в другую секцию),
        // Safe-контекст: safe, edit-note, edit-checklist, create-note, create-checklist
        $safeContextSections = ['safe-section', 'edit-note', 'edit-checklist', 'create-note', 'create-checklist'];
        $leavingSafeContext = in_array($this->section, $safeContextSections) && !in_array($section, $safeContextSections);
        if ($leavingSafeContext) {
            StateManager::remove('safe_unlocked');
        }

        // Если покидаем страницу создания или редактирования заметки, удаляем помеченные изображения
        if (($this->section === 'create-note' || $this->section === 'edit-note') &&
            $section !== 'create-note' && $section !== 'edit-note') {
            $temporaryImageService = app(TemporaryImageService::class);
            $temporaryImageService->cleanupPendingBackups();
        }

        StateManager::set('section', $section);
        StateManager::set('folderId', $folderId);
        StateManager::set('noteId', $noteId);

        $this->section = (string) $section;
        $this->folderId = $folderId;
        $this->noteId = $noteId;
        $this->componentKey++;

        $this->js('() => window.scrollTo({ top: 0, behavior: "smooth" })');

        $this->dispatch('stateUpdated', section:$section, folderId:$folderId);
        $this->dispatch('finishLoading');
    }

    public function render()
    {
        return view('livewire.layouts.app-layout');
    }
}
