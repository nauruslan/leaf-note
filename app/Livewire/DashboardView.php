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

    public function openChecklist($noteId)
    {
        $this->dispatch('navigateTo', section: 'checklist', folderId: $noteId);
    }

    public function createFolder($noteId)
    {
        $note = Note::where('user_id', Auth::id())->find($noteId);

        if (!$note) {
            return;
        }

        if ($note->folder) {
            $this->dispatch('navigateTo', section: 'folder', folderId: $note->folder->id);
        } else {
            // Если папки нет, можно перенаправить на создание папки или ничего не делать
            // Пока просто игнорируем
        }
    }

    public function getChecklistProgress($noteId): array
    {
        $note = Note::where('user_id', Auth::id())->find($noteId);

        if (!$note || !$note->isChecklist()) {
            return [
                'completed' => 0,
                'total' => 0,
                'percentage' => 0,
            ];
        }

        return $note->getChecklistProgress();
    }

    public function render()
    {
        $this->loadNotes();
        return view('livewire.dashboard', ['notes' => $this->notes]);
    }
}