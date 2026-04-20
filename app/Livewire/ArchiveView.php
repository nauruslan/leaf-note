<?php

namespace App\Livewire;

use App\Livewire\Traits\WithComponentPagination;
use App\Livewire\Traits\WithFiltering;
use App\Livewire\Traits\WithNoteCreating;
use App\Livewire\Traits\WithNoteOpening;
use App\Livewire\Traits\WithSearch;
use App\Models\Archive;
use App\Models\Note;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ArchiveView extends Component
{
    use WithComponentPagination;
    use WithSearch;
    use WithFiltering;
    use WithNoteCreating;
    use WithNoteOpening;

    public $heading = 'Архив';
    public $subheading = 'Заметки и списки помещенные в архив';

    public ?Archive $archive = null;

    public function mount(): void
    {
        $this->archive = Archive::where('user_id', Auth::id())->first();
    }


    #[Computed]
    public function totalArchivedNotesCount(): int
    {
        return Note::where('user_id', Auth::id())
            ->whereNotNull('archive_id')
            ->count();
    }

    #[Computed]
    public function notes(): LengthAwarePaginator
    {
        $query = Note::where('user_id', Auth::id())
            ->whereNotNull('archive_id')
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
        return view('livewire.archive');
    }
}