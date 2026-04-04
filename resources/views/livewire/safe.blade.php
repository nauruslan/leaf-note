<div>
    <!-- Модальное окно ввода пароля -->
    @if ($confirmingPassword && !$isUnlocked)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-100 mb-4">
                        <i data-lucide="lock" class="w-8 h-8 text-indigo-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">Введите пароль сейфа</h3>
                    <p class="text-gray-500 mt-1">Для доступа к защищённым заметкам введите пароль</p>
                </div>

                @if ($errorMessage)
                    <div class="mb-4 p-3 bg-red-100 border border-red-200 rounded-lg text-red-700 text-sm">
                        {{ $errorMessage }}
                    </div>
                @endif

                <form wire:submit="verifyPassword" class="space-y-4">
                    <div>
                        <input type="password" wire:model="password" placeholder="Пароль"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @if ($errorMessage) border-red-500 @endif"
                            autofocus>
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="closeModal"
                            class="px-5 py-2.5 text-gray-700 font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
                            Отмена
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                            Открыть сейф
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Header Section -->
    <header class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 ">
        <div class="bg-white rounded-b-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                        Сейф
                    </h1>
                    <p class="text-sm text-gray-500 mt-0.5">Защищённые заметки</p>
                </div>

                <div class="flex items-center gap-3">
                    <x-search wireModel="search" width="w-64" />
                </div>
            </div>
        </div>
    </header>

    @if ($isUnlocked)
        <!-- ControlPanel Section -->
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

        <!-- Content Section -->
        <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 mb-6 flex flex-wrap gap-5">
            @forelse($this->notes as $note)
                <x-card-minimal :note="$note" />
            @empty
                @if ($search)
                    <x-no-data icon="search-x" title="Совпадений не найдено"
                        description="Попробуйте изменить поисковый запрос" />
                @else
                    <x-no-data icon="lock" title="Сейф пуст" description="Создайте первую защищенную заметку" />
                @endif
            @endforelse
        </div>

        @if ($this->notes->hasPages())
            <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 mb-6">
                {{ $this->notes->links('livewire.pagination') }}
            </div>
        @endif
    @else
        <!-- Заблокированное состояние -->
        <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="flex items-center justify-center">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gray-100 mb-6">
                        <i data-lucide="lock" class="w-12 h-12 text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Сейф заблокирован</h3>
                    <p class="text-gray-500 mb-6 max-w-md mx-auto">
                        Введите пароль для доступа к защищённым заметкам
                    </p>
                    <button wire:click="$set('confirmingPassword', true)"
                        class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium rounded-lg shadow-md hover:shadow-lg transition-all inline-flex items-center gap-2">
                        <i data-lucide="key" class="w-5 h-5"></i>
                        Разблокировать
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
