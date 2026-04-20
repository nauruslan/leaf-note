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
        emptyIcon="star"
        emptyTitle="Избранных заметок нет"
        emptyDescription="Добавьте заметки в избранное, чтобы видеть их здесь"
        noResultsIcon="search-x"
        noResultsTitle="Совпадений не найдено"
        noResultsDescription="Попробуйте изменить поисковый запрос"
        noFilterResultsIcon="star"
        noFilterResultsTitle="Совпадений нет"
        noFilterResultsDescription="Попробуйте изменить фильтры"
    />
</div>
