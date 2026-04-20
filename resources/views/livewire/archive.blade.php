<div>
    <!-- Header Section -->
    <x-header :heading="$heading" :subheading="$subheading" showSearch />
    <!-- ControlPanel Section -->
    <x-notes-control-panel :perPage="$perPage" :filter="$filter" :sort="$sort" />
    <!-- Content Section -->
    <x-content-section
        :notes="$this->notes"
        :totalCount="$this->getTotalCount()"
        :search="$search"
        emptyIcon="package"
        emptyTitle="Архив пуст"
        emptyDescription="Сюда добавляются заметки, которые были восстановлены из корзины"
        noResultsIcon="search-x"
        noResultsTitle="Совпадений не найдено"
        noResultsDescription="Попробуйте изменить поисковый запрос"
        noFilterResultsIcon="package"
        noFilterResultsTitle="Совпадений нет"
        noFilterResultsDescription="Попробуйте изменить фильтры"
    />
</div>
