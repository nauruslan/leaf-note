<?php

namespace App\Livewire;

use App\Livewire\Traits\WithFavorite;
use App\Livewire\Traits\WithFiltering;
use App\Livewire\Traits\WithSearch;
use App\Livewire\Traits\WithComponentPagination;
use App\Models\Note;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class DashboardView extends Component
{
    use WithComponentPagination;
    use WithSearch;
    use WithFiltering;
    use WithFavorite;

    #[Computed]
    public function notes(): LengthAwarePaginator
    {
        $query = Note::where('user_id', Auth::id())
            ->whereNull('trash_id')
            ->whereNull('archive_id')
            ->whereNull('safe_id')
            ->with('folder');

        // Применяем фильтр
        $filterMap = [
            'notes' => ['column' => 'type', 'value' => Note::TYPE_NOTE],
            'checklists' => ['column' => 'type', 'value' => Note::TYPE_CHECKLIST],
        ];
        $query = $this->applyFilter($query, 'type', $filterMap);
        // Применяем сортировку (использует значения по умолчанию из трейта)
        $query = $this->applySorting($query);

        // Применяем поиск
        $query = $this->applySearch($query, ['title', 'payload']);

        // Пагинация
        return $query->paginate($this->perPage, ['*'], 'page', $this->page);
    }

    /**
     * Сбросить пагинацию при изменении любого из параметров: search, filter, sort.
     */
    public function updated($property): void
    {
        if (in_array($property, ['search', 'filter', 'sort'])) {
            $this->resetPagination();
        }
    }

    public function createNote(): void
    {
        $this->dispatch('navigateTo', 'create-note');
    }

    public function createChecklist(): void
    {
        $this->dispatch('navigateTo', 'create-checklist');
    }


    /**
     * Внутренний метод для открытия заметки или чеклиста.
     */
    public function openItem(int $noteId): void
    {
        $note = Note::where('user_id', Auth::id())->find($noteId);

        if (!$note) {
            return;
        }

        $section = $note->type === Note::TYPE_CHECKLIST ? 'edit-checklist' : 'edit-note';
        $this->dispatch('navigateTo', section: $section, noteId: $noteId);
    }

    /**
     * Открыть папку.
     */
    public function openFolder(int $folderId): void
    {
        if (!$folderId) {
            return;
        }

        $this->dispatch('stateUpdated', section: 'folder', folderId: $folderId);
        $this->dispatch('navigateTo', section: 'folder', folderId: $folderId);
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
