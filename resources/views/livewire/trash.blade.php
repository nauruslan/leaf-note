<div>
    <!-- Header Section -->
    <x-header :heading="$heading" :subheading="$this->totalCount > 0 ? 'Удалённые заметки и папки' : 'Корзина пуста'" showSearch />
    <!-- ControlPanel Section -->
    <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="bg-white rounded-xl shadow-md p-5">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <!-- Left Block: Actions -->
                <div class="flex flex-wrap items-center gap-3">
                    <x-button-restore-all wire:click="confirmRestoreAll" />
                    <x-button-delete-all wire:click="confirmEmptyTrash" />
                </div>
                <!-- Right Block: Filters -->
                <div class="flex flex-wrap items-center gap-4 justify-end">
                    <!-- Показать Dropdown -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Показать:</span>
                        <x-dropdown :options="[
                            ['value' => 12, 'text' => '12'],
                            ['value' => 24, 'text' => '24'],
                            ['value' => 36, 'text' => '36'],
                        ]" selected="{{ $perPage }}" wireModel="perPage" live
                            width="80px" />
                    </div>
                    <!-- Фильтр Dropdown -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Фильтр:</span>
                        <x-dropdown :options="[
                            ['value' => 'all', 'text' => 'Все'],
                            ['value' => 'notes', 'text' => 'Заметки'],
                            ['value' => 'checklists', 'text' => 'Списки'],
                            ['value' => 'folders', 'text' => 'Папки'],
                        ]" selected="{{ $filter }}" wireModel="filter" live
                            width="100px" />
                    </div>
                    <!-- Сортировка Dropdown -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Сортировка:</span>
                        <x-dropdown :options="[
                            ['value' => 'deleted', 'text' => 'По дате'],
                            ['value' => 'title', 'text' => 'По названию'],
                        ]" selected="{{ $sort }}" wireModel="sort" live
                            width="150px" />
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Content Section -->
    <div
        class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 mb-6 grid grid-cols-[repeat(auto-fill,minmax(320px,1fr))] gap-5">
        @php
            $showNotes = $filter === 'all' || $filter === 'notes' || $filter === 'checklists';
            $showFolders = $filter === 'all' || $filter === 'folders';
            $hasFolders = $showFolders && $this->trashedFolders->count() > 0;
            $hasNotes = $showNotes && $this->trashedNotes->count() > 0;
            $hasResults = $hasFolders || $hasNotes;
            $isSearching = !empty($search);
        @endphp
        @if ($hasResults)
            @if ($showFolders)
                @foreach ($this->trashedFolders as $folder)
                    <x-card :item="$folder" :section="$section" type="folder" />
                @endforeach
            @endif

            @if ($showNotes)
                @foreach ($this->trashedNotes as $note)
                    <x-card :item="$note" :color="$note->icon_color_class" :section="$section" />
                @endforeach
            @endif
        @endif
        {{-- Пустое состояние --}}
        @if ($this->totalCount === 0)
            <x-no-data icon="trash" title="Корзина пуста" description="Удалённые файлы будут отображаться здесь" />
        @endif
        {{-- Нет результатов поиска --}}
        @if (!$hasResults && $this->totalCount > 0 && $isSearching)
            <x-no-data icon="search-x" title="Совпадений не найдено"
                description="Попробуйте изменить поисковый запрос" />
        @endif
    </div>
    @if ($hasResults && $this->trashedNotes->hasPages())
        <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 mb-6">
            {{ $this->trashedNotes->links('livewire.pagination') }}
        </div>
    @endif
    <!-- Модальное окно подтверждения восстановления одного элемента -->
    <x-modal-confirm :show="$confirmingRestore" title="Восстановить?" :description="$restoreDescription" confirmText="Восстановить"
        cancelText="Отмена" confirmMethod="confirmRestore" cancelMethod="closeModal" confirmColor="indigo" />
    <!-- Модальное окно подтверждения удаления одного элемента -->
    <x-modal-confirm :show="$confirmingDeletion" title="Удалить навсегда?"
        description="Это действие необратимо. Элемент будет удален безвозвратно." confirmText="Удалить"
        cancelText="Отмена" confirmMethod="confirmDelete" cancelMethod="closeModal" confirmColor="red" />
    <!-- Модальное окно подтверждения восстановления всех элементов -->
    <x-modal-confirm :show="$confirmingRestoreAll" title="Восстановить всё?"
        description="Все удалённые элементы будут восстановлены" confirmText="Восстановить" cancelText="Отмена"
        confirmMethod="restoreAll" cancelMethod="closeRestoreAllModal" confirmColor="indigo" />
    <!-- Модальное окно подтверждения очистки корзины -->
    <x-modal-confirm :show="$confirmingEmptyTrash" title="Очистить корзину?"
        description="Все элементы в корзине будут удалены безвозвратно. Это действие необратимо." confirmText="Очистить"
        cancelText="Отмена" confirmMethod="emptyTrash" cancelMethod="closeEmptyTrashModal" confirmColor="red" />
</div>
