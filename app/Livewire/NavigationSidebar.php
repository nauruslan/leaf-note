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
        'folderCreated' => 'refreshSidebar',
        'folderDeleted' => 'refreshSidebar',
        'noteCreated' => 'refreshSidebar',
        'noteDeleted' => 'refreshSidebar',
        'checklistCreated' => 'refreshSidebar',
        'checklistDeleted' => 'refreshSidebar',
        'favoriteToggled' => 'refreshSidebar',
        'stateUpdated' => 'updateState'
    ];

    public function mount(): void
    {
        if (!Auth::check()) {
            return;
        }

        $this->section = StateManager::get('section', 'dashboard');
        $this->folderId = StateManager::get('folderId', null);
        $this->isExpanded = Session::get('sidebar_expanded', false);
    }

    #[Computed]
    public function noteCounts(): object
    {
        $userId = Auth::id();

        if (!$userId) {
            return (object) [
                'dashboard' => 0,
                'safe' => 0,
                'archive' => 0,
                'checklist' => 0,
                'favorite' => 0,
            ];
        }

        $counts = Note::where('user_id', $userId)
            ->selectRaw("
                COUNT(CASE WHEN trash_id IS NULL AND archive_id IS NULL AND safe_id IS NULL THEN 1 END) as dashboardCount,
                COUNT(CASE WHEN safe_id IS NOT NULL THEN 1 END) as safeCount,
                COUNT(CASE WHEN archive_id IS NOT NULL THEN 1 END) as archiveCount,
                COUNT(CASE WHEN type = ? AND trash_id IS NULL AND archive_id IS NULL AND safe_id IS NULL THEN 1 END) as checklistCount,
                COUNT(CASE WHEN is_favorite = 1 AND trash_id IS NULL AND archive_id IS NULL AND safe_id IS NULL THEN 1 END) as favoriteCount
            ", [Note::TYPE_CHECKLIST])
            ->first();

        return $counts;
    }

    #[Computed]
    public function trashCount(): int
    {
        $userId = Auth::id();
        if (!$userId) return 0;

        $notes = Note::where('user_id', $userId)
                     ->whereNotNull('trash_id')
                     ->select('id');

        $folders = Folder::where('user_id', $userId)
                         ->whereNotNull('trash_id')
                         ->select('id');

        return $notes->union($folders)->count();
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


    public function goTo(string $section, ?int $folderId = null, $isExpanded = true): void
    {
        if ($this->section === $section && $this->folderId === $folderId) {
            return;
        }

        $this->section = $section;
        $this->folderId = $folderId;

        Session::put('sidebar_expanded', $isExpanded);
        $this->isExpanded = $isExpanded;

        $this->dispatch('navigateTo', section: $section, folderId: $folderId);

        $this->js('window.scrollTo(0, 0)');
    }

    public function updateState(string $section, ?int $folderId = null): void
    {
        $this->section = $section;
        $this->folderId = $folderId;
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

    public function logout()
    {
        $this->js("localStorage.removeItem('sidebar_scroll')");

        app(Logout::class)();
        return redirect()->route('login');
    }

    public function render()
    {
        return view('livewire.navigation-sidebar');
    }
}
