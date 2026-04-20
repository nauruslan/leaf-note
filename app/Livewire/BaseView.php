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
use Livewire\Attributes\Locked;
use Livewire\Component;

abstract class BaseView extends Component
{
    use WithComponentPagination;
    use WithSearch;
    use WithFiltering;
    use WithNoteCreating;
    use WithNoteOpening;
    use WithFolderOpening;

    #[Locked]
    public string $heading = '';

    #[Locked]
    public string $subheading = '';

    /**
     * Получить карту фильтров для типов заметок.
     */
    protected function getFilterMap(): array
    {
        return [
            'notes' => ['column' => 'type', 'value' => Note::TYPE_NOTE],
            'checklists' => ['column' => 'type', 'value' => Note::TYPE_CHECKLIST],
        ];
    }

    /**
     * Базовые условия запроса для конкретного представления.
     * Переопределить в дочернем классе.
     *
     * @return array<string, mixed>
     */
    abstract protected function getBaseConditions(): array;

    /**
     * Получить общее количество заметок для текущего представления.
     */
    abstract protected function getTotalCount(): int;

    /**
     * Сбросить пагинацию при изменении фильтрующих параметров.
     */
    public function updated(string $property): void
    {
        $this->resetPaginationOnFilterChange($property);
    }

    /**
     * Общее количество заметок (с кэшированием).
     */
    #[Computed(cache: true, seconds: 10*60)]
    public function totalNotesCount(): int
    {
        return $this->getTotalCount();
    }

    /**
     * Получить заметки с применением фильтров, сортировки и поиска.
     */
    #[Computed]
    public function notes(): LengthAwarePaginator
    {
        $query = Note::forUser(Auth::id())
            ->with('folder');

        // Применяем базовые условия
        $this->applyBaseConditions($query);

        // Применяем фильтр
        $query = $this->applyFilter($query, 'type', $this->getFilterMap());

        // Применяем сортировку
        $query = $this->applySorting($query);

        // Применяем поиск
        $query = $this->applySearch($query, ['title', 'search_content']);

        // Пагинация
        return $query->paginate($this->perPage, ['*'], 'page', $this->page);
    }

    /**
     * Применить базовые условия к запросу.
     */
    protected function applyBaseConditions($query): void
    {
        foreach ($this->getBaseConditions() as $column => $value) {
            if ($value === null) {
                $query->whereNull($column);
            } elseif (str_starts_with($column, 'whereNotNull:')) {
                $query->whereNotNull(substr($column, 13));
            } else {
                $query->where($column, $value);
            }
        }
    }
}
