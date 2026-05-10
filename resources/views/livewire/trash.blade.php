<div>
    <!-- Header Section -->
    <x-header :heading="$heading" :subheading="$subheading ?: ($this->totalCount > 0 ? 'Удалённые заметки и папки' : 'Корзина пуста')" showSearch />
    <!-- ControlPanel Section -->
    <x-trash-control-panel :perPage="$perPage" :filter="$filter" :sort="$sort" />
    <!-- Content Section -->
    <x-trash-content-section :trashedNotes="$this->trashedNotes" :trashedFolders="$this->trashedFolders" :totalCount="$this->totalCount" :search="$search" :filter="$filter"
        :section="$section" emptyIcon="trash" emptyTitle="Корзина пуста"
        emptyDescription="Удалённые файлы будут отображаться здесь" noResultsIcon="search-x"
        noResultsTitle="Совпадений не найдено" noResultsDescription="Попробуйте изменить поисковый запрос"
        noFilterResultsIcon="trash" noFilterResultsTitle="Совпадений нет"
        noFilterResultsDescription="Попробуйте изменить фильтры" />
    <!-- Модальное окно подтверждения восстановления одного элемента -->
    <x-modal type="restore" :show="$confirmingRestore" title="Восстановить?" :description="$restoreDescription" confirmMethod="confirmRestore"
        cancelMethod="closeModal" />
    <!-- Модальное окно подтверждения удаления одного элемента -->
    <x-modal type="delete" :show="$confirmingDeletion" title="Удалить навсегда?"
        description="Это действие необратимо. Элемент будет удален безвозвратно." confirmMethod="confirmDelete"
        cancelMethod="closeModal" />
    <!-- Модальное окно подтверждения восстановления всех элементов -->
    <x-modal type="restore" :show="$confirmingRestoreAll" title="Восстановить всё?"
        description="Все удалённые элементы будут восстановлены" confirmMethod="restoreAll"
        cancelMethod="closeRestoreAllModal" />
    <!-- Модальное окно подтверждения очистки корзины -->
    <x-modal type="delete" :show="$confirmingEmptyTrash" title="Очистить корзину?"
        description="Все элементы в корзине будут удалены безвозвратно. Это действие необратимо."
        confirmMethod="emptyTrash" cancelMethod="closeEmptyTrashModal" />
</div>
