@props([
    'perPage' => 12,
    'filter' => 'all',
    'sort' => 'deleted',
])

<div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="bg-white rounded-xl shadow-md p-5">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <!-- Left Block: Actions -->
            <div class="flex flex-wrap items-center gap-3">
                <x-button-restore-all wire:click="confirmRestoreAll" :disabled="!$this->isTrashActive" />
                <x-button-delete-all wire:click="confirmEmptyTrash" :disabled="!$this->isTrashActive" />
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
                    ]" selected="{{ $sort }}" wireModel="sort" live width="150px" />
                </div>
            </div>
        </div>
    </div>
</div>
