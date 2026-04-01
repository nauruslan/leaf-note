<div>
    <!-- Header Section -->
    <header class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 ">
        <div class="bg-white rounded-b-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                        Корзина
                    </h1>
                    <p class="text-sm text-gray-500 mt-0.5">
                        {{ $this->totalCount > 0 ? 'Удалённые заметки и папки' : 'Корзина пуста' }}
                    </p>
                </div>

                <x-search wireModel="search" width="w-64" />
            </div>
        </div>
    </header>

    <!-- ControlPanel Section -->
    <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="bg-white rounded-xl shadow-md p-5">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <!-- Left Block: Actions -->
                <div class="flex flex-wrap items-center gap-3">
                    <button wire:click="$dispatch('confirmRestoreAll')"
                        class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium py-2.5 px-5 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                        <i data-lucide="refresh-ccw" class="w-4 h-4"></i>
                        Восстановить всё
                    </button>
                    <button wire:click="$dispatch('confirmEmptyTrash')"
                        class="bg-white border border-gray-300 hover:bg-red-50 text-red-600 font-medium py-2.5 px-5 rounded-lg shadow-sm hover:shadow transition-all flex items-center gap-2">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                        Очистить корзину
                    </button>
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
    <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 mb-6 flex flex-wrap gap-5">
        @php
            $showNotes = $filter === 'all' || $filter === 'notes' || $filter === 'checklists';
            $showFolders = $filter === 'all' || $filter === 'folders';
            $hasFolders = $showFolders && $this->trashedFolders->count() > 0;
            $hasNotes = $showNotes && $this->trashedNotes->count() > 0;
            $hasResults = $hasFolders || $hasNotes;
            $isSearching = !empty($search);
        @endphp

        {{-- Результаты поиска --}}
        @if ($hasResults)
            {{-- Папки в корзине --}}
            @if ($showFolders)
                @foreach ($this->trashedFolders as $folder)
                    <x-card-delete :item="$folder" type="folder" />
                @endforeach
            @endif

            {{-- Заметки в корзине --}}
            @if ($showNotes)
                @foreach ($this->trashedNotes as $note)
                    <x-card-delete :item="$note" type="note" />
                @endforeach
            @endif
        @endif

        {{-- Пустое состояние --}}
        @if ($this->totalCount === 0)
            <div class="w-full flex items-center justify-center py-20">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-100 mb-6">
                        <i data-lucide="trash" class="w-10 h-10 text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Корзина пуста</h3>
                    <p class="text-gray-500 mb-6 max-w-md mx-auto">
                        Удалённые заметки и папки будут отображаться здесь
                    </p>
                </div>
            </div>
        @endif

        {{-- Нет результатов поиска --}}
        @if (!$hasResults && $this->totalCount > 0 && $isSearching)
            <div class="w-full flex items-center justify-center py-20">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-100 mb-6">
                        <i data-lucide="search-x" class="w-10 h-10 text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Совпадений не найдено</h3>
                    <p class="text-gray-500 mb-6 max-w-md mx-auto">
                        Попробуйте изменить поисковый запрос
                    </p>
                    <button wire:click="goTo('dashboard')"
                        class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium py-2.5 px-5 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center gap-2 mx-auto">
                        <i data-lucide="layout-grid" class="w-4 h-4"></i>
                        Вернуться на главную доску
                    </button>
                </div>
            </div>
        @endif
    </div>

    @if ($hasResults && $this->trashedNotes->hasPages())
        <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 mb-6">
            {{ $this->trashedNotes->links('livewire.pagination') }}
        </div>
    @endif

    <!-- Модальное окно подтверждения восстановления одного элемента -->
    <x-modal-confirm
        :show="$confirmingRestore"
        title="Восстановить?"
        description="Элемент будет восстановлен и перемещен в архив."
        confirmText="Восстановить"
        cancelText="Отмена"
        confirmMethod="confirmRestore"
        cancelMethod="closeModal"
        confirmColor="indigo"
    />

    <!-- Модальное окно подтверждения удаления одного элемента -->
    <x-modal-confirm
        :show="$confirmingDeletion"
        title="Удалить навсегда?"
        description="Это действие необратимо. Элемент будет удален безвозвратно."
        confirmText="Удалить"
        cancelText="Отмена"
        confirmMethod="confirmDelete"
        cancelMethod="closeModal"
        confirmColor="red"
    />

    <!-- Модальное окно подтверждения восстановления всех элементов -->
    <x-modal-confirm
        :show="$confirmingRestoreAll"
        title="Восстановить всё?"
        description="Все удалённые элементы будут восстановлены и перемещены в архив."
        confirmText="Восстановить"
        cancelText="Отмена"
        confirmMethod="restoreAll"
        cancelMethod="closeRestoreAllModal"
        confirmColor="indigo"
    />

    <!-- Модальное окно подтверждения очистки корзины -->
    <x-modal-confirm
        :show="$confirmingEmptyTrash"
        title="Очистить корзину?"
        description="Все элементы в корзине будут удалены безвозвратно. Это действие необратимо."
        confirmText="Очистить"
        cancelText="Отмена"
        confirmMethod="emptyTrash"
        cancelMethod="closeEmptyTrashModal"
        confirmColor="red"
    />
</div>
