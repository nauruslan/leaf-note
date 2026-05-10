<div>
    @if (!$this->folder)
        <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <x-no-data icon="folder-x" title="Папка не найдена"
                description="Папка была удалена или у вас нет к ней доступа" />
        </div>
    @else
        <!-- Header Section -->
        <x-header :heading="$this->folder->title" :section="$section" showSearch />
        <!-- ControlPanel Section -->
        <x-notes-control-panel :perPage="$perPage" :filter="$filter" :sort="$sort" />
        <!-- Content Section -->
        <x-content-section :notes="$this->notes" :totalCount="$this->totalFolderNotesCount" :search="$search" :section="$section"
            emptyIcon="{{ $this->folder->icon }}" emptyTitle="Папка пуста"
            emptyDescription="Создайте заметку, чтобы увидеть её здесь" noResultsIcon="search-x"
            noResultsTitle="Совпадений не найдено" noResultsDescription="Попробуйте изменить поисковый запрос"
            noFilterResultsIcon="{{ $this->folder->icon }}" noFilterResultsTitle="Совпадений нет"
            noFilterResultsDescription="Попробуйте изменить фильтры" />
        <!-- Delete Confirmation Modal -->
        <x-modal type="delete" title="Удалить папку?"
            description="Папка будет перемещена в корзину. Вы сможете восстановить её позже." :show="$confirmingDeletion"
            confirmMethod="deleteFolder" cancelMethod="closeModal" />
    @endif
</div>
