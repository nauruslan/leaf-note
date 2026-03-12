<?php
namespace App\Livewire;

use App\Livewire\Actions\Logout;
use App\Models\Folder;
use App\Models\Note;
use App\Services\StateManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Computed;
use Livewire\Component;

class NavigationSidebar extends Component
{
    public string $section = 'dashboard';
    public ?int $folderId = null;
    public bool $isExpanded = false;

    protected $listeners = [
        'sidebarScrolled' => 'handleSidebarScrolled',
        'stateUpdated' => 'updateState',
        'folderCreated' => 'refreshFolders',
        'folderDeleted' => 'refreshFolders',
        'noteCreated' => 'refreshFolders',
        'checklistCreated' => 'refreshFolders',
        'noteDeleted' => 'refreshFolders',
    ];

    public function mount(): void
    {
        $this->section = StateManager::get('section', 'dashboard');
        $this->folderId = StateManager::get('folderId');
        $this->isExpanded = Session::get('sidebar_expanded', false);
    }

    public function updateState(string $section, ?int $folderId): void
    {
        // Для note оставляем активную вкладку dashboard
        $this->section = ($section === 'note') ? 'dashboard' : $section;
        $this->folderId = $folderId;
    }

    public static function invalidateCountCache(?string $section = null): void
    {
        $userId = Auth::id();
        if (!$userId) return;

        $sections = $section
            ? [$section]
            : ['dashboard', 'safe', 'archive', 'checklist', 'favorite', 'trash'];

        foreach ($sections as $key) {
            Cache::forget("user.{$userId}.counts.{$key}");
        }
    }

    #[Computed]
    public function dashboardCount(): int
    {
        $userId = Auth::id();
        if (!$userId) return 0;

        return Cache::remember(
            "user.{$userId}.counts.dashboard",
            now()->addMinutes(30),
            fn() => Note::where('user_id', $userId)
                ->whereNull('trash_id')
                ->whereNull('archive_id')
                ->whereNull('safe_id')
                ->count()
        );
    }

     #[Computed]
    public function safeCount(): int
    {
        $userId = Auth::id();
        if (!$userId) return 0;

        return Cache::remember(
            "user.{$userId}.counts.safe",
            now()->addMinutes(30),
            fn() => Note::where('user_id', $userId)
                ->whereNotNull('safe_id')
                ->count()
        );
    }

    #[Computed]
    public function archiveCount(): int
    {
        $userId = Auth::id();
        if (!$userId) return 0;

        return Cache::remember(
            "user.{$userId}.counts.archive",
            now()->addMinutes(30),
            fn() => Note::where('user_id', $userId)
                ->whereNotNull('archive_id')
                ->count()
        );
    }

    #[Computed]
    public function checklistCount(): int
    {
        $userId = Auth::id();
        if (!$userId) return 0;

        return Cache::remember(
            "user.{$userId}.counts.checklist",
            now()->addMinutes(30),
            fn() => Note::where('user_id', $userId)
                ->where('type', Note::TYPE_CHECKLIST)
                ->whereNull('trash_id')
                ->whereNull('archive_id')
                ->whereNull('safe_id')
                ->count()
        );
    }

    #[Computed]
    public function favoriteCount(): int
    {
        $userId = Auth::id();
        if (!$userId) return 0;

        return Cache::remember(
            "user.{$userId}.counts.favorite",
            now()->addMinutes(30),
            fn() => Note::where('user_id', $userId)
                ->where('is_favorite', true)
                ->whereNull('trash_id')
                ->whereNull('archive_id')
                ->whereNull('safe_id')
                ->count()
        );
    }

    #[Computed]
    public function trashCount(): int
    {
        $userId = Auth::id();
        if (!$userId) return 0;

        return Cache::remember(
            "user.{$userId}.counts.trash",
            now()->addMinutes(30),
            fn() => Note::where('user_id', $userId)->whereNotNull('trash_id')->count()
                + Folder::where('user_id', $userId)->whereNotNull('trash_id')->count()
        );
    }

    #[Computed]
    public function folders(): Collection
    {
        $userId = Auth::id();

        if (!$userId) {
            return collect();
        }

        return Cache::remember(
            "user.{$userId}.folders.active",
            now()->addMinutes(30),
            fn() => Folder::where('user_id', $userId)
                ->active()
                ->orderBy('title')
                ->get()
        );
    }

    public function refreshFolders(): void
    {
        $userId = Auth::id();
        if ($userId) {
            Cache::forget("user.{$userId}.folders.active");
        }
    }

    public function navigateTo(string $section, ?int $folderId = null): void
    {
        if ($this->section === $section && $this->folderId === $folderId) {
            return;
        }

        Session::put('sidebar_expanded', true);
        $this->isExpanded = true;

        $this->dispatch('navigateTo', section: $section, folderId: $folderId);

        $this->js('window.scrollTo(0, 0)');
    }

    public function clearSidebarFlag(): void
    {
        Session::forget('sidebar_expanded');
        $this->isExpanded = false;
    }

    public function logout()
    {
        // Очищаем LocalStorage через JavaScript
        $this->js('localStorage.clear()');
        // $this->js("localStorage.removeItem('sidebar_scroll');

        app(Logout::class)();
        return redirect()->route('login');
    }

    public function render()
    {
        return view('livewire.navigation-sidebar', [
            'folders' => $this->folders,
        ]);
    }
}