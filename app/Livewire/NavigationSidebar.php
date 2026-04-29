<?php
namespace App\Livewire;

use App\Livewire\Actions\Logout;
use App\Models\Folder;
use App\Models\Note;
use App\Models\Safe;
use App\Services\StateManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Computed;
use Livewire\Component;

class NavigationSidebar extends Component
{
    public string $section = 'dashboard-section';
    public ?int $folderId = null;
    public bool $isExpanded = false;
    public bool $confirmingLogout = false;
    public bool $isLoading = false;
    public ?string $loadingSection = null;
    public ?string $previousSection = null;
    public ?int $previousFolderId = null;


    protected $listeners = [
        'refreshSidebar' => 'refreshSidebar',
        'stateUpdated' => 'updateState',
        'startLoading' => 'startLoading',
        'finishLoading' => 'finishLoading',
    ];

    public function mount(): void
    {
        if (!Auth::check()) {
            return;
        }

        $this->section = StateManager::get('section', 'dashboard-section');
        $this->folderId = StateManager::get('folderId', null);
        $this->previousSection = StateManager::get('previous_section', null);
        $this->previousFolderId = StateManager::get('previous_folderId', null);
        $this->isExpanded = Session::get('sidebar_expanded', false);
    }

    /**
     * Возвращает секцию для подсветки активного элемента в сайдбаре.
     * Если текущая секция является "дочерней" (edit-note, edit-checklist),
     * возвращается предыдущая секция для сохранения подсветки.
     */
    #[Computed]
    public function activeSection(): string
    {
        $editingSections = ['edit-note', 'edit-checklist', 'edit-folder'];

        if (in_array($this->section, $editingSections) && $this->previousSection) {
            return $this->previousSection;
        }

        return $this->section;
    }

    #[Computed]
    public function userId(): ?int
    {
        return Auth::id();
    }

    #[Computed]
    public function noteCounts(): object
    {

        if (!$this->userId) {
            return (object) [
                'dashboard' => 0,
                'safe' => 0,
                'archive' => 0,
                'checklist' => 0,
                'favorite' => 0,
            ];
        }

        $counts = Note::where('user_id', $this->userId)
            ->selectRaw("
                COUNT(CASE WHEN trash_id IS NULL AND archive_id IS NULL AND safe_id IS NULL THEN 1 END) as dashboard,
                COUNT(CASE WHEN safe_id IS NOT NULL THEN 1 END) as safe,
                COUNT(CASE WHEN archive_id IS NOT NULL THEN 1 END) as archive,
                COUNT(CASE WHEN is_favorite = 1 AND trash_id IS NULL AND archive_id IS NULL AND safe_id IS NULL THEN 1 END) as favorite
            ")
            ->first();

        return $counts;
    }

    #[Computed]
    public function trashCount(): int
    {
        if (!$this->userId) {
            return 0;
        }

        $notesCount = Note::where('user_id', $this->userId)
            ->whereNotNull('trash_id')
            ->count();

        $foldersCount = Folder::where('user_id', $this->userId)
            ->whereNotNull('trash_id')
            ->count();

        return $notesCount + $foldersCount;
    }

    #[Computed]
    public function folders(): Collection
    {

        if (!$this->userId) {
            return collect();
        }

        return Folder::where('user_id', $this->userId)
            ->active()
            ->orderBy('title')
            ->withCount(['activeNotes as notes_count' => function ($query) {
                $query->whereNull('trash_id')
                      ->whereNull('archive_id')
                      ->whereNull('safe_id');
            }])
            ->get();
    }


    public function goTo(string $section, ?int $folderId = null, $isExpanded = true): void
    {
        if ($this->section === $section && $this->folderId === $folderId) {
            return;
        }

        $this->isLoading = true;
        $this->loadingSection = $section;

        // Сохраняем текущую секцию как предыдущую перед переходом
        $this->previousSection = $this->section;
        $this->previousFolderId = $this->folderId;

        $this->section = $section;
        $this->folderId = $folderId;

        // Сохраняем состояние в сессию
        StateManager::set('section', $section);
        StateManager::set('folderId', $folderId);
        StateManager::set('previous_section', $this->previousSection);
        StateManager::set('previous_folderId', $this->previousFolderId);

        Session::put('sidebar_expanded', $isExpanded);
        $this->isExpanded = $isExpanded;

        // Для сейфа - проверить нужно ли показывать модальное окно пароля
        if ($section === 'safe') {
            $safe = Safe::where('user_id', Auth::id())->first();
            if ($safe && $safe->hasPassword()) {
                // Сейф защищён паролем - отправить событие для открытия модального окна
                $this->dispatch('openSafePasswordModal');
            }
        }
$this->dispatch('navigateTo', section: $section, folderId: $folderId);
$this->dispatch('startLoading', section: $section, folderId: $folderId);

$this->js('window.scrollTo(0, 0)');
}

public function startLoading(string $section, ?int $folderId = null): void
{
$this->isLoading = true;
$this->loadingSection = $section;
}

public function finishLoading(): void
{
$this->isLoading = false;
$this->loadingSection = null;
}

public function updateState(string $section, ?int $folderId = null): void
{
    // Сохраняем текущую секцию как предыдущую перед обновлением
    $this->previousSection = $this->section;
    $this->previousFolderId = $this->folderId;

    $this->section = $section;
    $this->folderId = $folderId;

    // Сохраняем состояние в сессию
    StateManager::set('section', $section);
    StateManager::set('folderId', $folderId);
    StateManager::set('previous_section', $this->previousSection);
    StateManager::set('previous_folderId', $this->previousFolderId);
}

    public function refreshSidebar(): void
    {
        $this->dispatch('$refresh');
    }



    public function clearSidebarFlag(): void
    {
        Session::forget('sidebar_expanded');
        $this->isExpanded = false;
    }

    public function confirmLogout(): void
    {
        $this->confirmingLogout = true;
    }

    public function closeLogoutModal(): void
    {
        $this->confirmingLogout = false;
        $this->dispatch('modalClosed');
    }

    public function logout()
    {
        $this->closeLogoutModal();
        $this->js("localStorage.removeItem('sidebar_scroll')");

        app(Logout::class)();
        return redirect()->route('login');
    }

    public function render()
    {
        return view('livewire.navigation-sidebar');
    }
}