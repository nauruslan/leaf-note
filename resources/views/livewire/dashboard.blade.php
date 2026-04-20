<div>
    <!-- Header Section -->
    <x-header :heading="$heading" :subheading="$subheading" :showSearch="true" searchWireModel="search" />
    <!-- ControlPanel Section -->
    <x-notes-control-panel :perPage="$perPage" :filter="$filter" :sort="$sort" />
    <!-- Content Section -->
    <x-content-section :notes="$this->notes" :totalCount="$this->getTotalCount()" :search="$search" emptyIcon="layout-grid"
        emptyTitle="Заметок нет" emptyDescription="Создайте заметку, чтобы увидеть её здесь" noResultsIcon="search-x"
        noResultsTitle="Совпадений не найдено" noResultsDescription="Попробуйте изменить поисковый запрос"
        noFilterResultsIcon="layout-grid" noFilterResultsTitle="Совпадений нет"
        noFilterResultsDescription="Попробуйте изменить фильтры" />
</div>
