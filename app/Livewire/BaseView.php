<?php

namespace App\Livewire;

use App\Livewire\Traits\WithComponentPagination;
use App\Livewire\Traits\WithFiltering;
use App\Livewire\Traits\WithFolderOpening;
use App\Livewire\Traits\WithNoteOpening;
use App\Livewire\Traits\WithSearch;
use App\Models\Note;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

abstract class BaseView extends Component
{
    use WithComponentPagination;
    use WithSearch;
    use WithFiltering;
    use WithNoteOpening;
    use WithFolderOpening;

    /**
     * Префикс для условия whereNotNull в getBaseConditions().
     * Использование: 'whereNotNull:column_name' вместо прямого условия.
     */
    protected const WHERE_NOT_NULL_PREFIX = 'whereNotNull:';

    /**
     * Скоупы модели для применения к запросу.
     * Переопределить в дочернем классе.
     *
     * @var array<string>
     */
    protected array $scopes = [];

    /**
     * ID папки для фильтрации (опционально).
     */
    public ?int $folderId = null;

    /**
     * Показывать ли удалённые элементы.
     */
    public bool $withTrashed = false;

    public string $heading = '';
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
     * Формат массива:
     * - ['column' => 'value'] => where('column', 'value')
     * - ['column' => null] => whereNull('column')
     * - ['whereNotNull:column' => true] => whereNotNull('column')
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
     * Получить базовый запрос заметок.
     */
    protected function buildNotesQuery(): Builder
    {
        $query = Note::forUser(Auth::id())
            ->with('folder');

        // Фильтрация по папке
        if ($this->folderId !== null) {
            $query->where('folder_id', $this->folderId);
        }

        // Показывать удалённые (для корзины)
        if ($this->withTrashed) {
            $query->withTrashed();
        }

        // Применяем скоупы
        $this->applyScopes($query);

        // Применяем базовые условия
        $this->applyBaseConditions($query);

        return $query;
    }

    /**
     * Получить заметки с применением фильтров, сортировки и поиска.
     */
    #[Computed]
    public function notes(): LengthAwarePaginator
    {
        $query = $this->buildNotesQuery();

        // Применяем фильтр
        $query = $this->applyFilters($query, 'type');

        // Применяем сортировку
        $query = $this->applySorting($query);

        // Применяем поиск
        $query = $this->applySearch($query, ['title', 'search_content']);

        // Пагинация
        return $query->paginate($this->perPage, ['*'], 'page', $this->page);
    }

    /**
     * Применить скоупы модели к запросу.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    protected function applyScopes(\Illuminate\Database\Eloquent\Builder $query): void
    {
        foreach ($this->scopes as $scope) {
            if (method_exists($query->getModel(), 'scope' . ucfirst($scope))) {
                $query->$scope();
            }
        }
    }

    /**
     * Применить базовые условия к запросу.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    protected function applyBaseConditions(\Illuminate\Database\Eloquent\Builder $query): void
    {
        foreach ($this->getBaseConditions() as $column => $value) {
            // Проверка валидности имени колонки
            if (!is_string($column) || $column === '') {
                continue;
            }

            // Обработка префикса whereNotNull
            if (str_starts_with($column, self::WHERE_NOT_NULL_PREFIX)) {
                $realColumn = substr($column, strlen(self::WHERE_NOT_NULL_PREFIX));
                if ($realColumn === '') {
                    continue;
                }
                $query->whereNotNull($realColumn);
                continue;
            }

            // Обработка null значения
            if ($value === null) {
                $query->whereNull($column);
                continue;
            }

            // Стандартное условие where
            $query->where($column, $value);
        }
    }
}