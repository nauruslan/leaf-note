<?php

namespace App\Livewire;

use App\Livewire\Traits\WithComponentPagination;
use App\Livewire\Traits\WithFavorite;
use App\Livewire\Traits\WithFiltering;
use App\Livewire\Traits\WithSearch;
use App\Models\Folder;
use App\Models\Note;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class FolderView extends Component
{
    use WithComponentPagination;
    use WithSearch;
    use WithFiltering;
    use WithFavorite;

    public string $section = 'folder';
    public ?int $folderId = null;
    public bool $confirmingDeletion = false;

    protected $listeners = [
        'stateUpdated' => 'updateState',
        'noteAdded' => 'refreshCurrentFolder',
        'noteDeleted' => 'refreshCurrentFolder',
        'closeModal' => 'closeModal',
    ];

    #[Computed]
    public function folder(): ?Folder
    {
        $userId = Auth::id();

        return Folder::where('user_id', $userId)
            ->where('id', $this->folderId)
            ->active()
            ->first();
    }

    #[Computed]
    public function notes(): LengthAwarePaginator
    {
        if (!$this->folderId) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage);
        }

        $query = Note::where('user_id', Auth::id())
            ->where('folder_id', $this->folderId)
            ->with('folder');

        // Применяем фильтр
        $filterMap = [
            'notes' => ['column' => 'type', 'value' => Note::TYPE_NOTE],
            'checklists' => ['column' => 'type', 'value' => Note::TYPE_CHECKLIST],
        ];
        $query = $this->applyFilter($query, 'type', $filterMap);

        // Применяем сортировку
        $query = $this->applySorting($query);

        // Применяем поиск
        $query = $this->applySearch($query, ['title', 'payload']);

        // Пагинация
        return $query->paginate($this->perPage, ['*'], 'page', $this->page);
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'filter', 'sort'])) {
            $this->resetPagination();
        }
    }

    public function updateState(string $section, ?int $folderId): void
    {
        $this->section = $section;
        $this->folderId = $folderId;
    }

    public function createNote(): void
    {
        $this->dispatch('navigateTo', 'create-note');
    }

    public function createChecklist(): void
    {
        $this->dispatch('navigateTo', 'create-checklist');
    }

    public function openItem(int $noteId): void
    {
        $note = Note::where('user_id', Auth::id())->find($noteId);

        if (!$note) {
            return;
        }

        $section = $note->type === Note::TYPE_CHECKLIST ? 'edit-checklist' : 'edit-note';
        $this->dispatch('navigateTo', section: $section, noteId: $noteId);
    }

    public function confirmDeletion(): void
    {
        $this->confirmingDeletion = true;
    }

    public function closeModal(): void
    {
        $this->confirmingDeletion = false;
    }

    public function deleteFolder(?int $folderId = null): void
    {
        $folder = $this->folder;

        if ($folderId !== null) {
            $folder = Folder::where('user_id', Auth::id())->find($folderId);
        }

        if (!$folder) {
            $this->confirmingDeletion = false;
            return;
        }

        $success = $folder->moveToTrash();

        if ($success) {
            // После удаления перенаправить на дашборд
            $this->dispatch('navigateTo', 'dashboard');
            // Уведомить навигацию об удалении папки
            $this->dispatch('folderDeleted');
            // Закрыть модальное окно
            $this->confirmingDeletion = false;
        } else {
            // Ошибка, например, корзина переполнена
        }
    }

    public function openEditFolder($id): void
    {
        if (!$this->folder) {
            return;
        }

        $this->dispatch('navigateTo', section: 'edit-folder', folderId: $id);
    }

    public function render()
    {
        return view('livewire.folder');
    }
}