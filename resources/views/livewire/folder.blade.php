<div>
    <!-- Header HeaderPage -->
    <header class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 ">
        <div class="bg-white rounded-b-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                        {{ $this->folder->title }}
                    </h1>
                    <div class="flex items-center gap-3 mt-1">
                        <button class="text-gray-500 hover:text-indigo-600 focus:outline-none" title="Редактировать папку"
                            wire:click="openEditFolder({{ $this->folder->id }})">
                            Редактировать
                        </button>|
                        <button class="text-gray-500 hover:text-red-600 focus:outline-none" title="Удалить папку"
                            wire:click="confirmDeletion">Удалить
                        </button>
                    </div>
                </div>

                <x-search wireModel="search" width="w-64" />
            </div>
        </div>
    </header>

    <!-- ControlPanel -->
    <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="bg-white rounded-xl shadow-md p-5">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <!-- Left Block: Create Buttons -->
                <div class="flex flex-wrap items-center gap-3">
                    <x-button-create-note wire:click="createNote">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        Новая заметка
                    </x-button-create-note>
                    <x-button-create-checklist wire:click="createChecklist">
                        <i data-lucide="list" class="w-4 h-4"></i>
                        Новый список
                    </x-button-create-checklist>
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
                        ]" selected="{{ $filter }}" wireModel="filter" live
                            width="100px" />
                    </div>

                    <!-- Сортировка Dropdown -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Сортировка:</span>
                        <x-dropdown :options="[
                            ['value' => 'updated', 'text' => 'По дате'],
                            ['value' => 'title', 'text' => 'По названию'],
                        ]" selected="{{ $sort }}" wireModel="sort" live
                            width="140px" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content ContentPage -->
    <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 mb-6 flex flex-wrap gap-5">
        @forelse($this->notes as $note)
            <x-card :note="$note" />
        @empty
            <!-- Состояние: нет заметок -->
            <div class="w-full flex items-center justify-center py-20">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-100 mb-6">
                        <i data-lucide="file-text" class="w-10 h-10 text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">В этой папке пока нет заметок</h3>
                    <p class="text-gray-500 mb-6 max-w-md mx-auto">
                        Создайте первую заметку, чтобы увидеть её здесь
                    </p>
                    <x-button-create-note wire:click="createNote" class="px-6 inline-flex">
                        <i data-lucide="plus" class="w-5 h-5"></i>
                        Создать заметку
                    </x-button-create-note>
                </div>
            </div>
        @endforelse
    </div>

    @if ($this->notes->hasPages())
        <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 mb-6">
            {{ $this->notes->links('livewire.pagination') }}
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    <x-modal-delete title="Удалить папку?"
        description="Папка будет перемещена в корзину. Вы сможете восстановить её позже." :confirmingDeletion="$confirmingDeletion"
        closeMethod="closeModal" deleteMethod="deleteFolder" />
</div>
