<div wire:poll.{{ $attemptResetPollInterval }}ms="checkAttempts">
    {{-- Модальное окно предупреждения о незащищённом сейфе --}}
    <x-modal type="info" :show="$showUnprotectedModal" title="Сейф не защищён"
        description="Пароль для сейфа не установлен. Вы можете установить его в разделе Профиль." icon="lock"
        confirmMethod="closeModal" />
    @if ($isUnlocked)
        <!-- Header Section -->
        <x-header :heading="$heading" :subheading="$subheading" showSearch />
        <!-- ControlPanel Section -->
        <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="bg-white rounded-xl shadow-md p-5">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <!-- Left Block: Create Buttons -->
                    <div class="flex flex-wrap items-center gap-3">
                        <x-button-create-note wire:click="createNote" />
                        <x-button-create-checklist wire:click="createChecklist" />
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
        <div
            class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 mb-6 grid grid-cols-[repeat(auto-fill,minmax(320px,1fr))] gap-5">
            @forelse($this->notes as $note)
                <x-card :item="$note" :color="$note->color" />
            @empty
                <div class="col-span-full">
                    @if ($this->getTotalCount() === 0)
                        <x-no-data icon="lock" title="Сейф пуст" description="Создайте первую защищенную заметку" />
                    @elseif ($search)
                        <x-no-data icon="search-x" title="Совпадений не найдено"
                            description="Попробуйте изменить поисковый запрос" />
                    @else
                        <x-no-data icon="lock" title="Совпадений нет" description="Попробуйте изменить фильтры" />
                    @endif
                </div>
            @endforelse
        </div>
        @if ($this->notes->hasPages())
            <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 mb-6">
                {{ $this->notes->links('livewire.pagination') }}
            </div>
        @endif
    @else
        <!-- Заблокированное состояние -->
        <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 py-24">
            <div class="flex items-center justify-center">
                <div class="text-center max-w-[400px]">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-100 mb-4">
                        <i data-lucide="lock" class="w-8 h-8 text-indigo-600"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Введите пароль сейфа</h3>
                    <p class="text-gray-500 mb-3 max-w-md mx-auto">
                        Для доступа к защищённым заметкам введите пароль
                    </p>
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
                        <div class="pt-2">
                            <button type="submit"
                                class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium py-3 px-4 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2">
                                <i data-lucide="lock" class="w-4 h-4"></i>
                                Открыть сейф
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
