<?php
namespace App\Livewire;

use App\Livewire\Actions\Logout;
use App\Services\FolderService;
use App\Services\NavigationService;
use App\Services\SafeAuthService;
use App\Services\StatisticsService;
use App\Services\StateManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Session;
use Livewire\Component;

class NavigationSidebar extends Component
{
    // UI State - публичные свойства для привязки
    public bool $confirmingLogout = false;
    public bool $isLoading = false;
    public ?string $loadingSection = null;

    // Состояние навигации - хранится в сессии
    #[Session]
    public string $section = 'dashboard-section';

    #[Session]
    public ?int $folderId = null;

    #[Session]
    public ?string $previousSection = null;

    #[Session]
    public ?int $previousFolderId = null;

    // Внедряемые сервисы
    protected StatisticsService $statisticsService;
    protected NavigationService $navigationService;
    protected FolderService $folderService;
    protected SafeAuthService $safeAuthService;

    /**
     * Инициализация сервисов
     */
    public function boot(
        StatisticsService $statisticsService,
        NavigationService $navigationService,
        FolderService $folderService,
        SafeAuthService $safeAuthService,
    ): void {
        $this->statisticsService = $statisticsService;
        $this->navigationService = $navigationService;
        $this->folderService = $folderService;
        $this->safeAuthService = $safeAuthService;
    }

    /**
     * Инициализация компонента
     */
    public function mount(): void
    {
        if (!Auth::check()) {
            return;
        }

        // Загружаем состояние из StateManager
        $this->section = StateManager::get('section', 'dashboard-section');
        $this->folderId = StateManager::get('folderId', null);
        $this->previousSection = StateManager::get('previous_section', null);
        $this->previousFolderId = StateManager::get('previous_folderId', null);
    }

    /**
     * Возвращает секцию для подсветки активного элемента в сайдбаре
     */
    #[Computed]
    public function activeSection(): string
    {
        return $this->navigationService->getActiveSection(
            $this->section,
            $this->previousSection
        );
    }

    /**
     * ID текущего пользователя
     */
    #[Computed]
    public function userId(): ?int
    {
        return Auth::id();
    }

    /**
     * Статистика заметок для сайдбара
     */
    #[Computed]
    public function noteCounts(): object
    {
        if (!$this->userId) {
            return (object) [
                'dashboard' => 0,
                'safe' => 0,
                'archive' => 0,
                'favorite' => 0,
            ];
        }

        $dto = $this->statisticsService->getNoteCounts($this->userId);

        return (object) [
            'dashboard' => $dto->dashboard,
            'safe' => $dto->safe,
            'archive' => $dto->archive,
            'favorite' => $dto->favorite,
        ];
    }

    /**
     * Количество элементов в корзине
     */
    #[Computed]
    public function trashCount(): int
    {
        if (!$this->userId) {
            return 0;
        }

        return $this->statisticsService->getTrashCount($this->userId);
    }

    /**
     * Список папок с количеством заметок
     */
    #[Computed]
    public function folders(): Collection
    {
        if (!$this->userId) {
            return new Collection();
        }

        return $this->folderService->getFoldersWithNotesCount($this->userId);
    }

    /**
     * Переход к секции
     */
    #[Locked]
    public function goTo(string $section, ?int $folderId = null): void
    {

        if ($this->section === $section && $this->folderId === $folderId) {
            return;
        }

        $this->isLoading = true;
        $this->loadingSection = $section;

        // Используем NavigationService для правильного сохранения предыдущей секции
        $this->navigationService->navigateTo(
            $section,
            $folderId,
            null, // noteId
            $this->section,
            $this->folderId,
            null // currentNoteId
        );

        // Получаем обновленное состояние из StateManager
        $this->section = StateManager::get('section');
        $this->folderId = StateManager::get('folderId');
        $this->previousSection = StateManager::get('previous_section');
        $this->previousFolderId = StateManager::get('previous_folderId');

        // Проверяем пароль сейфа
        if ($section === 'safe-section' && $this->safeAuthService->shouldOpenPasswordModal(Auth::id())) {
            $this->dispatch('openSafePasswordModal');
        }

        $this->dispatch('navigateTo', section: $section, folderId: $folderId);
        $this->dispatch('startLoading', section: $section, folderId: $folderId);

        // Удаляем прямую прокрутку, так как она будет обрабатываться в AppLayout
        // Это предотвращает конфликт с пагинацией
    }

    /**
     * Начало загрузки
     */
    #[On('startLoading')]
    public function startLoading(string $section, ?int $folderId = null): void
    {
        $this->isLoading = true;
        $this->loadingSection = $section;
    }

    /**
     * Завершение загрузки
     */
    #[On('finishLoading')]
    public function finishLoading(): void
    {
        $this->isLoading = false;
        $this->loadingSection = null;
    }

    /**
     * Обновление состояния из внешнего источника
     */
    #[On('stateUpdated')]
    public function updateState(string $section, ?int $folderId = null): void
    {

        // Обновляем свойства без перезаписи предыдущей секции
        $this->section = $section;
        $this->folderId = $folderId;

        $this->previousSection = StateManager::get('previous_section');
        $this->previousFolderId = StateManager::get('previous_folderId');

        // Сохраняем состояние в StateManager
        StateManager::set('section', $this->section);
        StateManager::set('folderId', $this->folderId);
    }

    /**
     * Обновление сайдбара
     */
    #[On('refreshSidebar')]
    public function refreshSidebar(): void
    {
        $this->dispatch('$refresh');
    }

    /**
     * Подтверждение выхода
     */
    public function confirmLogout(): void
    {
        $this->confirmingLogout = true;
    }

    /**
     * Закрытие модального окна выхода
     */
    public function closeLogoutModal(): void
    {
        $this->confirmingLogout = false;
        $this->dispatch('modalClosed');
    }

    /**
     * Выход из системы
     */
    #[Locked]
    public function logout()
    {
        $this->closeLogoutModal();
        $this->js("localStorage.removeItem('sidebar_scroll')");

        app(Logout::class)();
        return redirect()->route('login');
    }

    /**
     * Рендер компонента
     */
    public function render(): \Illuminate\View\View
    {
        return view('livewire.navigation-sidebar');
    }
}
