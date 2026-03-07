<?php
namespace App\Livewire;

use App\Models\Note;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DashboardView extends Component
{
    public string $search = '';

    protected $notes;

    protected $listeners = [
        'stateUpdated' => 'updateState',
        'noteCreated' => 'loadNotes',
        'noteDeleted' => 'loadNotes',
    ];

    public function mount(): void
    {
        $this->loadNotes();
    }

    public function updateState(string $section, ?int $folderId, string $search): void
    {
        $this->search = $search;
        $this->loadNotes();
    }

    public function loadNotes(): void
    {
        $this->notes = Note::where('user_id', Auth::id())
            ->where('type', Note::TYPE_NOTE)
            ->whereNull('trash_id')
            ->whereNull('archive_id')
            ->whereNull('safe_id')
            ->with('folder')
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    public function createNote()
    {
        $this->dispatch('navigateTo', 'create-note');
    }

    public function createChecklist()
    {
        $this->dispatch('navigateTo', 'create-checklist');
    }

    public function toggleFavorite($noteId)
    {
        $note = Note::where('user_id', Auth::id())->find($noteId);

        if ($note) {
            $note->toggleFavorite();
            $this->loadNotes();
            $this->dispatch('noteCreated');
        }
    }

    public function openNote($noteId)
    {
        $this->dispatch('navigateTo', section: 'note', folderId: $noteId);
    }

    public function render()
    {
        $this->loadNotes();
        return view('livewire.dashboard', ['notes' => $this->notes]);
    }
}
