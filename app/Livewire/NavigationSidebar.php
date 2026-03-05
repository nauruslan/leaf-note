<?php
namespace App\Livewire;

use App\Livewire\Actions\Logout;
use App\Models\Folder;
use App\Services\StateManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;
use Livewire\Attributes\On;

class NavigationSidebar extends Component
{
    public string $section = 'dashboard';
    public ?int $folderId = null;
    public bool $isExpanded = false;

    /** @var Collection<int, Folder> */
    public $folders = [];

    protected $listeners = [
        'sidebarScrolled' => 'handleSidebarScrolled',
    ];

    public function mount(): void
    {
        logger()->debug('navigation-sidebar mount', ['id' => $this->getId()]);
        $this->section = StateManager::get('section', 'dashboard');
        $this->folderId = StateManager::get('folderId');
        $this->isExpanded = Session::get('sidebar_expanded', false);

        $this->loadFolders();
    }

    #[On('stateUpdated')]
    public function updateState(string $section, ?int $folderId): void
    {
        $this->section  = $section;
        $this->folderId = $folderId;
    }

    private function loadFolders(): void
    {
        $userId = Auth::id();
        if (!$userId) {
            $this->folders = collect();
            return;
        }

        $this->folders = Folder::where('user_id', $userId)
            ->orderBy('title')
            ->get();
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
        logger()->debug('Rendering navigation-sidebar', [
            'id' => $this->getId(),
            'section' => $this->section,
            'folderId' => $this->folderId
        ]);
        return view('livewire.navigation-sidebar');
    }
}