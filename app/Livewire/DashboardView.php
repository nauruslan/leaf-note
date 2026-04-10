<?php

namespace App\Livewire;

use App\Livewire\Traits\WithComponentPagination;
use App\Livewire\Traits\WithFiltering;
use App\Livewire\Traits\WithFolderOpening;
use App\Livewire\Traits\WithNoteCreating;
use App\Livewire\Traits\WithNoteOpening;
use App\Livewire\Traits\WithSearch;
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
    use WithNoteCreating;
    use WithNoteOpening;
    use WithFolderOpening;

    public $heading='Главная доска';
    public $subheading='Все ваши заметки и списки в одном месте';

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
        $query = $this->applySearch($query, ['title', 'search_content']);

        // Пагинация
        return $query->paginate($this->perPage, ['*'], 'page', $this->page);
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}