<?php

namespace App\Livewire\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Трейт для поиска по заметкам.
 * Используется совместно с WithServerPagination.
 *
 * Публичные свойства:
 * - search: string - поисковый запрос
 *
 * Защищённые свойства (можно переопределить в компоненте):
 * - allowedSearchColumns: array - разрешённые колонки для поиска (whitelist)
 * - minSearchLength: int - минимальная длина поискового запроса
 *
 * Требует наличия WithServerPagination трейта.
 */
trait WithSearch
{
    /**
     * Разрешённые колонки для поиска (whitelist).
     */
    protected array $allowedSearchColumns = ['title', 'search_content'];

    /**
     * Минимальная длина поискового запроса.
     */
    protected int $minSearchLength = -1;

    public string $search = '';

    /**
     * Применить поисковый запрос к builder-у.
     *
     * @param Builder $query
     * @param array $columns - колонки для поиска (по умолчанию ['title', 'search_content'])
     * @param int|null $minLength - минимальная длина для поиска (null = использовать $minSearchLength)
     * @return Builder
     */
    protected function applySearch(Builder $query, array $columns = ['title', 'search_content'], ?int $minLength = null): Builder
    {
        $effectiveMinLength = $minLength ?? $this->getMinSearchLength();

        if (strlen(trim($this->search)) < $effectiveMinLength) {
            return $query;
        }

        // Фильтруем колонки через whitelist
        $validColumns = array_intersect($columns, $this->allowedSearchColumns);
        if (empty($validColumns)) {
            return $query;
        }

        $words = preg_split('/\s+/', trim($this->search));

        return $query->where(function ($q) use ($words, $validColumns) {
            foreach ($words as $word) {
                $lowerWord = mb_strtolower($word);
                $q->where(function ($sub) use ($lowerWord, $validColumns) {
                    foreach ($validColumns as $column) {
                        $sub->orWhereRaw('LOWER(' . $column . ') LIKE ?', ['%' . $lowerWord . '%']);
                    }
                });
            }
        });
    }

    /**
     * Получить минимальную длину поискового запроса.
     * Если $minSearchLength равен -1, используется значение по умолчанию 1.
     */
    protected function getMinSearchLength(): int
    {
        return $this->minSearchLength === -1 ? 1 : $this->minSearchLength;
    }

    /**
     * Сбросить поиск.
     */
    public function resetSearch(): void
    {
        $this->search = '';
    }
}