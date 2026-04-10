<?php

namespace App\Livewire\Traits;

/**
 * Трейт для поиска по заметкам.
 * Используется совместно с WithServerPagination.
 *
 * Публичные свойства:
 * - search: string - поисковый запрос
 *
 * Требует наличия WithServerPagination трейта.
 */
trait WithSearch
{
    public string $search = '';

    /**
     * Применить поисковый запрос к builder-у.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $columns - колонки для поиска (по умолчанию ['title', 'search_content'])
     * @param int $minLength - минимальная длина для поиска
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applySearch($query, array $columns = ['title', 'search_content'], int $minLength = 1)
    {
        if (strlen(trim($this->search)) < $minLength) {
            return $query;
        }

        $words = preg_split('/\s+/', trim($this->search));

        return $query->where(function ($q) use ($words, $columns) {
            foreach ($words as $word) {
                $lowerWord = mb_strtolower($word);
                $q->where(function ($sub) use ($lowerWord, $columns) {
                    foreach ($columns as $index => $column) {
                        $method = $index === 0 ? 'whereRaw' : 'orWhereRaw';
                        $sub->$method('LOWER(' . $column . ') LIKE ?', ['%' . $lowerWord . '%']);
                    }
                });
            }
        });
    }

    // Сбросить поиск
    public function resetSearch(): void
    {
        $this->search = '';
    }
}
