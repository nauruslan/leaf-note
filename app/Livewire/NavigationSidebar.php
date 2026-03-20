<?php
namespace App\Livewire;

use App\Livewire\Actions\Logout;
use App\Models\Folder;
use App\Models\Note;
use App\Services\StateManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Computed;
use Livewire\Component;

class NavigationSidebar extends Component
{
    public string $section = 'dashboard';
    public ?int $folderId = null;
    public bool $isExpanded = false;

    protected $listeners = [
        'stateUpdated' => 'updateState',
        'folderCreated' => 'refreshFolders',
        'folderDeleted' => 'refreshFolders',
        'noteCreated' => 'refreshFolders',
        'noteDeleted' => 'refreshFolders',
        'checklistCreated' => 'refreshFolders',
        'checklistDeleted' => 'refreshFolders',
        'favoriteToggled' => 'refreshFavoriteCount',
    ];

    public function mount(): void
    {
        if (!Auth::check()) {
            return;
        }

        $this->section = StateManager::get('section', 'dashboard');
        $this->folderId = StateManager::get('folderId');
        $this->isExpanded = Session::get('sidebar_expanded', false);
    }

    #[Computed]
    public function dashboardCount(): int
    {
        $userId = Auth::id();
        if (!$userId) return 0;

        return Note::where('user_id', $userId)
            ->whereNull('trash_id')
            ->whereNull('archive_id')
            ->whereNull('safe_id')
            ->count();
    }

     #[Computed]
    public function safeCount(): int
    {
        $userId = Auth::id();
        if (!$userId) return 0;

        return Note::where('user_id', $userId)
            ->whereNotNull('safe_id')
            ->count();
    }

    #[Computed]
    public function archiveCount(): int
    {
        $userId = Auth::id();
        if (!$userId) return 0;

        return Note::where('user_id', $userId)
            ->whereNotNull('archive_id')
            ->count();
    }

    #[Computed]
    public function checklistCount(): int
    {
        $userId = Auth::id();
        if (!$userId) return 0;

        return Note::where('user_id', $userId)
            ->where('type', Note::TYPE_CHECKLIST)
            ->whereNull('trash_id')
            ->whereNull('archive_id')
            ->whereNull('safe_id')
            ->count();
    }

    #[Computed]
    public function favoriteCount(): int
    {
        $userId = Auth::id();
        if (!$userId) return 0;

        return Note::where('user_id', $userId)
            ->where('is_favorite', true)
            ->whereNull('trash_id')
            ->whereNull('archive_id')
            ->whereNull('safe_id')
            ->count();
    }

    #[Computed]
    public function trashCount(): int
    {
        $userId = Auth::id();
        if (!$userId) return 0;

        return Note::where('user_id', $userId)->whereNotNull('trash_id')->count()
            + Folder::where('user_id', $userId)->whereNotNull('trash_id')->count();
    }

    #[Computed]
    public function folders(): Collection
    {
        $userId = Auth::id();

        if (!$userId) {
            return collect();
        }

        return Folder::where('user_id', $userId)
            ->active()
            ->orderBy('title')
            ->withCount(['activeNotes as notes_count' => function ($query) {
                $query->whereNull('trash_id')
                      ->whereNull('archive_id')
                      ->whereNull('safe_id');
            }])
            ->get();
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

    public function updateState(string $section, ?int $folderId): void
    {
        $this->section = $section;
        $this->folderId = $folderId;
    }

    public function refreshFolders(): void
    {
        $this->dispatch('$refresh');
    }


    public function refreshFavoriteCount(): void
    {
        $this->dispatch('$refresh');
    }


    public function clearSidebarFlag(): void
    {
        Session::forget('sidebar_expanded');
        $this->isExpanded = false;
    }

    public function logout()
    {
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
