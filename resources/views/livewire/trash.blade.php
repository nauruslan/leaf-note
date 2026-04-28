<div>
    <!-- Header Section -->
    <x-header :heading="$heading" :subheading="$subheading ?: ($this->totalCount > 0 ? 'Удалённые заметки и папки' : 'Корзина пуста')" showSearch />
    <!-- ControlPanel Section -->
    <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="bg-white rounded-xl shadow-md p-5">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <!-- Left Block: Actions -->
                <div class="flex flex-wrap items-center gap-3">
                    <x-button-restore-all wire:click="confirmRestoreAll" :class="$this->totalCount ? '' : '!cursor-not-allowed'" />
                    <x-button-delete-all wire:click="confirmEmptyTrash" :class="$this->totalCount ? '' : '!cursor-not-allowed'" />
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
            $isSearching = !empty($search);
            $showNotes = $isSearching || $filter === 'all' || $filter === 'notes' || $filter === 'checklists';
            $showFolders = $isSearching || $filter === 'all' || $filter === 'folders';
            $hasFolders = $showFolders && $this->trashedFolders->count() > 0;
            $hasNotes = $showNotes && $this->trashedNotes->count() > 0;
            $hasResults = $hasFolders || $hasNotes;
        @endphp
        @php
            $itemsToShow = collect();
            if ($showFolders) {
                $itemsToShow = $itemsToShow->merge(
                    $this->trashedFolders->map(fn($folder) => (object) ['type' => 'folder', 'data' => $folder]),
                );
            }
            if ($showNotes) {
                $itemsToShow = $itemsToShow->merge(
                    $this->trashedNotes->map(fn($note) => (object) ['type' => 'note', 'data' => $note]),
                );
            }
        @endphp

        @forelse($itemsToShow as $item)
            @if ($item->type === 'folder')
                <x-card :item="$item->data" :section="$section" type="folder" />
            @else
                <x-card :item="$item->data" :color="$item->data->color" :section="$section" />
            @endif
        @empty
            <div class="col-span-full">
                @if ($this->totalCount === 0)
                    <x-no-data icon="trash" title="Корзина пуста"
                        description="Удалённые файлы будут отображаться здесь" />
                @elseif ($isSearching)
                    <x-no-data icon="search-x" title="Совпадений не найдено"
                        description="Попробуйте изменить поисковый запрос" />
                @else
                    <x-no-data icon="trash" title="Совпадений нет" description="Попробуйте изменить фильтры" />
                @endif
            </div>
        @endforelse
    </div>
    @if ($hasResults && $this->trashedNotes->hasPages())
        <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 mb-6">
            {{ $this->trashedNotes->links('livewire.pagination') }}
        </div>
    @endif
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
