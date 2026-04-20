<?php

namespace App\Livewire\Traits;

/**
 * Трейт для серверной пагинации с сохранением состояния.
 * Используется вместо стандартного WithPagination.
 *
 * Публичные свойства:
 * - page: int - текущая страница
 * - perPage: int - элементов на страницу
 */
trait WithComponentPagination
{
    /**
     * Константы
     */
    public const DEFAULT_PER_PAGE = 12;
    public const PER_PAGE_OPTIONS = [12, 24, 36];

    public int $page = 1;
    public int $perPage = self::DEFAULT_PER_PAGE;

    /**
     * Перейти на конкретную страницу
     */
    public function gotoPage(int $page): void
    {
        $this->page = max(1, $page);
    }

    /**
     * Следующая страница
     */
    public function nextPage(): void
    {
        $this->page++;
    }

    /**
     * Предыдущая страница
     */
    public function previousPage(): void
    {
        if ($this->page > 1) {
            $this->page--;
        }
    }

    /**
     * Сбросить пагинацию на первую страницу
     */
    public function resetPagination(): void
    {
        $this->page = 1;
    }

    /**
     * Обновить пагинацию при изменении perPage
     */
    public function updatedPerPage(): void
    {
        $this->resetPagination();
    }

    /**
     * Инициализация пагинации (вызвать в mount)
     */
    protected function initPagination(int $defaultPerPage = self::DEFAULT_PER_PAGE): void
    {
        $this->perPage = request()->get('perPage', $defaultPerPage);
    }

    /**
     * Получить параметры пагинации для пагинатора
     */
    protected function getPaginationParams(): array
    {
        return ['page', 'perPage'];
    }

    /**
     * Сбросить пагинацию при изменении фильтрующих параметров.
     * Вызвать из updated() в компоненте.
     */
    public function resetPaginationOnFilterChange(string $property): void
    {
        if (in_array($property, ['search', 'filter', 'sort'])) {
            $this->resetPagination();
        }
    }
}