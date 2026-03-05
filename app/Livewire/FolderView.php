<?php
namespace App\Livewire;

use App\Models\Folder;
use App\Services\StateManager;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;

/**
 * @property-read ?Folder $folder   // только для IDE, не публично
 */
class FolderView extends Component
{
    public string $section = 'folder';
    public string $search = '';
    public ?int $folderId = null;
    public $folders = [];

    protected ?Folder $folder = null;

    protected $listeners = [
        'noteAdded'   => 'refreshCurrentFolder',
        'noteDeleted' => 'refreshCurrentFolder',
    ];

    public function mount(): void
    {
        $this->section  = StateManager::get('section', 'folder');
        $this->search   = StateManager::get('search', '');
        $this->folderId = StateManager::get('folderId');

        $this->loadAllFolders();
        $this->loadCurrentFolder();
    }

    #[On('stateUpdated')]
    public function updateState(string $section, ?int $folderId, string $search): void
    {
        $this->section  = $section;
        $this->folderId = $folderId;
        $this->search   = $search;

        $this->loadCurrentFolder();
    }

    private function loadAllFolders(): void
    {
        $query = Folder::where('user_id', Auth::id())
            ->orderBy('title');

        if ($this->search) {
            $query->where('title', 'like', "%{$this->search}%");
        }

        $this->folders = $query->get();
    }

    private function loadCurrentFolder(): void
    {
        $this->folder = $this->folderId
            ? Folder::where('user_id', Auth::id())->find($this->folderId)
            : null;
    }

    public function refreshCurrentFolder(): void
    {
        $this->loadCurrentFolder();
    }

    public function render()
    {
        return view('livewire.folder', [
            'folder' => $this->folder,
        ]);
    }
}