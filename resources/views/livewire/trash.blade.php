<div>
    <!-- Header Section -->
    <x-header :heading="$this->heading" :subheading="$this->subheading ?: ($this->totalTrashCount > 0 ? 'Удалённые заметки и папки' : 'Корзина пуста')" showSearch />
    <!-- ControlPanel Section -->
    <x-trash-control-panel :perPage="$perPage" :filter="$filter" :sort="$sort" />
    <!-- Content Section -->
    <x-trash-content-section :trashedNotes="$this->notes" :trashedFolders="$this->trashedFolders" :totalCount="$this->totalTrashCount" :search="$search" :filter="$filter"
        :section="$section" emptyIcon="trash" emptyTitle="Корзина пуста"
        emptyDescription="Удалённые файлы будут отображаться здесь" noResultsIcon="search-x"
        noResultsTitle="Совпадений не найдено" noResultsDescription="Попробуйте изменить поисковый запрос"
        noFilterResultsIcon="trash" noFilterResultsTitle="Совпадений нет"
        noFilterResultsDescription="Попробуйте изменить фильтры" />
    <!-- Модальное окно подтверждения восстановления одного элемента -->
    <x-modal type="restore" :show="$this->isModalOpen('restore')" title="Восстановить?" :description="$this->getModalData('restore', 'description')" confirmMethod="confirmRestore"
        cancelMethod="closeModal('restore')" />
    <!-- Модальное окно подтверждения удаления одного элемента -->
    <x-modal type="delete" :show="$this->isModalOpen('delete')" :title="$this->getModalTitle('delete')" :description="$this->getModalDescription('delete')" confirmMethod="confirmDeleteItem"
        cancelMethod="closeModal('delete')" />
    <!-- Модальное окно подтверждения восстановления всех элементов -->
    <x-modal type="restore" :show="$this->isModalOpen('restoreAll')" :title="$this->getModalData('restoreAll', 'title')" :description="$this->getModalData('restoreAll', 'description')" confirmMethod="restoreAll"
        cancelMethod="closeModal('restoreAll')" />
    <!-- Модальное окно подтверждения очистки корзины -->
    <x-modal type="delete" :show="$this->isModalOpen('emptyTrash')" :title="$this->getModalData('emptyTrash', 'title')" :description="$this->getModalData('emptyTrash', 'description')" confirmMethod="emptyTrash"
        cancelMethod="closeModal('emptyTrash')" />
</div>
