<div>
    @if (!$this->folder)
        <div class="fixed inset-x-0 top-0 bottom-20 flex items-center justify-center bg-indigo-10">
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
        <x-modal type="delete" :show="$this->isModalOpen('delete')" :title="$this->getModalTitle('delete')" :description="$this->getModalDescription('delete')" confirmMethod="deleteFolder"
            cancelMethod="closeModal('delete')" />
    @endif
</div>
