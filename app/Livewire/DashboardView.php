<?php
namespace App\Livewire;

use App\Models\Note;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithoutUrlPagination;

class DashboardView extends Component
{
    use WithoutUrlPagination;

    public string $search = '';
    public string $filter = 'all';
    public string $sort = 'updated';
    public int $perPage = 12;
    public int $page = 1;

    protected $listeners = [
        'stateUpdated' => 'updateState',
        // 'noteCreated' => 'resetPagination',
        // 'noteDeleted' => 'resetPagination',
    ];

    public function updateState(string $section, ?int $folderId, string $search): void
    {
        // Игнорируем, так как локальный поиск и навигация не влияют на dashboard
    }

    #[Computed]
    public function notes()
    {
        $query = Note::where('user_id', Auth::id())
            ->whereNull('trash_id')
            ->whereNull('archive_id')
            ->whereNull('safe_id')
            ->with('folder');

        // Фильтр по типу
        if ($this->filter === 'notes') {
            $query->where('type', Note::TYPE_NOTE);
        } elseif ($this->filter === 'checklists') {
            $query->where('type', Note::TYPE_CHECKLIST);
        }

        // Поиск
        if (strlen(trim($this->search)) >= 2) {
            $words = preg_split('/\s+/', trim($this->search));
            $query->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $q->where(function ($sub) use ($word) {
                        $sub->where('title', 'LIKE', '%' . $word . '%')
                            ->orWhere('payload', 'LIKE', '%' . $word . '%');
                    });
                }
            });
        }

        // Сортировка
        if ($this->sort === 'updated') {
            $query->orderBy('updated_at', 'desc');
        } elseif ($this->sort === 'title') {
            $query->orderBy('title', 'asc');
        }

        // Пагинация
        return $query->paginate($this->perPage, ['*'], 'page', $this->page);
    }

    public function gotoPage($page)
    {
        $this->page = $page;
    }

    public function nextPage()
    {
        $this->page++;
    }

    public function previousPage()
    {
        if ($this->page > 1) {
            $this->page--;
        }
    }

    public function resetPagination()
    {
        $this->page = 1;
    }

    public function updatedFilter()
    {
        $this->resetPagination();
    }

    public function updatedSort()
    {
        $this->resetPagination();
    }

    public function updatedPerPage()
    {
        $this->resetPagination();
    }

    public function updatedSearch()
    {
        $this->resetPagination();
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
            $wasFavorite = $note->is_favorite;
            $note->toggleFavorite();

            // Диспатчим событие для обновления sidebar
            $this->dispatch('favoriteToggled',
                noteId: $note->id,
                isFavorite: $note->is_favorite,
                wasFavorite: $wasFavorite
            );
        }
    }

    public function openNote($noteId)
    {
        $this->dispatch('navigateTo', section: 'edit-note', folderId: $noteId);
    }

    public function openChecklist($noteId)
    {
        $this->dispatch('navigateTo', section: 'edit-checklist', folderId: $noteId);
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
        }
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}