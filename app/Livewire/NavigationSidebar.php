<?php
namespace App\Livewire;

use App\Livewire\Actions\Logout;
use App\Models\Folder;
use App\Services\StateManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class NavigationSidebar extends Component
{
    public string $section = 'dashboard';
    public ?int $folderId = null;
    public bool $isExpanded = false;
    public ?int $scrollPosition = null; // Позиция скролла
    public $folders = [];

    protected $listeners = [
        'stateUpdated' => 'updateState',
        'sidebarScrolled' => 'handleSidebarScrolled',
    ];

    public function mount(): void
    {
        $this->section = StateManager::get('section', 'dashboard');
        $this->folderId = StateManager::get('folderId');
        $this->isExpanded = Session::get('sidebar_expanded', false);
        $this->scrollPosition = Session::get('sidebar_scroll_position', 0);

        $userId = Auth::id();
        if (!$userId) {
            $this->folders = collect();
            return;
        }
        $this->folders = Folder::where('user_id', $userId)->orderBy('title')->get();
    }

    public function updateState($section, $folderId)
    {
        $this->section  = $section;
        $this->folderId = $folderId;

        // Принудительно восстановим скролл после обновления
        $this->dispatch('restoreScroll', scrollPosition: $this->scrollPosition);
    }

    public function navigateTo(string $section, ?int $folderId = null): void
    {
        if ($this->section === $section && $this->folderId === $folderId) {
            return;
        }

        Session::put('sidebar_expanded', true);

        $this->dispatch('navigateTo', section: $section, folderId: $folderId);
    }

    public function clearSidebarFlag(): void
    {
        Session::forget('sidebar_expanded');
        $this->isExpanded = false;
    }

    // Сохраняем позицию скролла
    public function handleSidebarScrolled($position): void
    {
        $this->scrollPosition = $position;
        Session::put('sidebar_scroll_position', $position);
    }

    public function logout()
    {
        app(Logout::class)();
        return redirect()->route('login');
    }

    public function render()
    {
        return view('livewire.navigation-sidebar');
    }
}
