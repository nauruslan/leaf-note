<?php

namespace App\Livewire\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Трейт для фильтрации и сортировки.
 * Используется совместно с WithServerPagination.
 *
 * Публичные свойства:
 * - filter: string - текущий фильтр
 * - sort: string - текущая сортировка
 *
 * Защищённые свойства (можно переопределить в компоненте):
 * - sortMap: array - карта сортировок (по умолчанию updated/title)
 * - sortDirections: array - направления сортировок (по умолчанию desc/asc)
 */
trait WithFiltering
{
    public string $filter = 'all';
    public string $sort = 'updated';

    /**
     * Карта сортировок.
     */
    protected array $sortMap = [
        'updated' => 'updated_at',
        'title' => 'title',
    ];

    /**
     * Направления сортировки.
     */
    protected array $sortDirections = [
        'updated' => 'desc',
        'title' => 'asc',
    ];


    /**
     * Применить фильтр к builder-у.
     *
     * @param Builder $query
     * @param string $typeColumn - колонка типа (по умолчанию 'type')
     * @param array $filterMap - карта фильтров, например ['notes' => ['column' => 'type', 'value' => Note::TYPE_NOTE]]
     * @return Builder
     */
    protected function applyFilter(Builder $query, string $typeColumn = 'type', array $filterMap = []): Builder
    {
        if ($this->filter === 'all' || !isset($filterMap[$this->filter])) {
            return $query;
        }

        $config = $filterMap[$this->filter];

        if (isset($config['whereNull'])) {
            return $query->whereNull($config['whereNull']);
        }

        return $query->where($config['column'] ?? $typeColumn, $config['value'] ?? $this->filter);
    }

    /**
     * Применить сортировку к builder-у.
     *
     * @param Builder $query
     * @param array $sortMap - карта сортировок, например ['updated' => 'updated_at', 'title' => 'title']
     * @param array $sortDirections - направления, например ['title' => 'asc']
     * @return Builder
     */
    protected function applySort(Builder $query, array $sortMap = [], array $sortDirections = []): Builder
    {
        $column = $sortMap[$this->sort] ?? 'updated_at';
        $direction = $sortDirections[$this->sort] ?? 'desc';

        return $query->orderBy($column, $direction);
    }

    /**
     * Применить фильтр к builder-у, используя $this->filterMap.
     *
     * @param Builder $query
     * @param string $typeColumn
     * @return Builder
     */
    protected function applyFilters(Builder $query, string $typeColumn = 'type'): Builder
    {
        return $this->applyFilter($query, $typeColumn, $this->filterMap);
    }

    /**
     * Применить сортировку к builder-у, используя $this->sortMap и $this->sortDirections.
     *
     * @param Builder $query
     * @return Builder
     */
    protected function applySorting(Builder $query): Builder
    {
        return $this->applySort($query, $this->sortMap, $this->sortDirections);
    }

    /**
     * Применить все фильтры и сортировку.
     *
     * @param Builder $query
     * @param string $typeColumn
     * @return Builder
     */
    protected function applyFiltersAndSort(Builder $query, string $typeColumn = 'type'): Builder
    {
        return $this->applySorting(
            $this->applyFilters($query, $typeColumn)
        );
    }

    /**
     * Сбросить фильтры и сортировку
     */
    public function resetFilters(): void
    {
        $this->filter = 'all';
        $this->sort = 'updated';
    }
}
