<?php

namespace App\Livewire\Layouts;

use App\Services\AppLayoutService;
use App\Services\DemoUserService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Attributes\Session;
use Livewire\Component;

class AppLayout extends Component
{
    #[Session]
    public string $section = 'dashboard-section';

    #[Session]
    public ?int $folderId = null;

    #[Session]
    public ?int $noteId = null;

    public int $componentKey = 0;
    public bool $showDemoModal = false;
    public string $demoExpirationTime = '';
    public bool $isLoading = false;
    public ?string $loadingSection = null;
    public ?int $loadingNoteId = null;

    private AppLayoutService $appLayoutService;

    public function boot(AppLayoutService $appLayoutService): void
    {
        $this->appLayoutService = $appLayoutService;
    }

    public function mount(): void
    {
        $this->initializeState();
        $this->checkNotifications();
    }

    /**
     * Инициализация состояния компонента
     */
    private function initializeState(): void
    {
        $state = $this->appLayoutService->initializeComponentState();
        $this->section = $state['section'];
        $this->folderId = $state['folderId'];
        $this->noteId = $state['noteId'];

        $this->initializeDemoModal();
    }

    /**
     * Проверка и отправка уведомлений
     */
    private function checkNotifications(): void
    {
        $notificationService = app(NotificationService::class);
        $notification = $notificationService->checkSafePasswordResetNotification();

        if ($notification) {
            $this->dispatch('notification', $notification);
        }
    }

    /**
     * Инициализация модального окна для демо-пользователей
     */
    private function initializeDemoModal(): void
    {
        $user = Auth::user();

        if (!$user) {
            return;
        }

        $demoData = $this->appLayoutService->initializeDemoModal($user);
        $this->showDemoModal = $demoData['showDemoModal'];
        $this->demoExpirationTime = $demoData['demoExpirationTime'];

        // Если модальное окно было показано, отмечаем это в сессии
        if ($this->showDemoModal) {
            $demoUserService = app(DemoUserService::class);
            $demoUserService->markDemoModalShown();
        }
    }

    /**
     * Закрыть модальное окно демо-информации
     */
    public function closeDemoModal(): void
    {
        $this->showDemoModal = false;
    }

    /**
     * Начать загрузку
     */
    #[On('startLoading')]
    public function startLoading(string $section, ?int $folderId = null, ?int $noteId = null): void
    {
        $loadingData = $this->appLayoutService->prepareLoadingData($section, $folderId, $noteId);
        $this->isLoading = $loadingData['isLoading'];
        $this->loadingSection = $loadingData['loadingSection'];
        $this->loadingNoteId = $loadingData['loadingNoteId'];
    }

    /**
     * Завершить загрузку
     */
    #[On('finishLoading')]
    public function finishLoading(): void
    {
        $loadingData = $this->appLayoutService->resetLoadingData();
        $this->isLoading = $loadingData['isLoading'];
        $this->loadingSection = $loadingData['loadingSection'];
        $this->loadingNoteId = $loadingData['loadingNoteId'];
    }

    /**
     * Выполнить навигацию
     */
    #[On('navigateTo')]
    public function navigateTo(string $section, ?int $folderId = null, ?int $noteId = null): void
    {
        $navigationData = $this->appLayoutService->prepareNavigationData($section, $folderId, $noteId);

        // Очищаем помеченные на удаление изображения, если уходим со страницы редактирования/создания заметки
        $previousSection = $navigationData['previousSection'] ?? null;
        $previousNoteId = $navigationData['previousNoteId'] ?? null;

        if (in_array($previousSection, ['create-note', 'edit-note'])) {
            $temporaryImageService = app(\App\Services\TemporaryImageService::class);
            $temporaryImageService->cleanupPendingBackups($previousNoteId);
        }

        // Обновляем свойства компонента
        $this->section = $navigationData['section'];
        $this->folderId = $navigationData['folderId'];
        $this->noteId = $navigationData['noteId'];
        $this->componentKey++;

        // Отправляем события перед прокруткой
        $this->dispatch('stateUpdated', section: $section, folderId: $folderId);
        $this->dispatch('finishLoading');

        // Прокрутка страницы наверх с задержкой, чтобы убедиться, что DOM обновлен
        $this->js('setTimeout(() => window.scrollTo({ top: 0, behavior: "smooth" }), 50)');
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.layouts.app-layout');
    }
}
