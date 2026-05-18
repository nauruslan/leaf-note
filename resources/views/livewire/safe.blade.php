<div wire:poll.{{ $attemptResetPollInterval }}ms="checkAttempts" wire:init="checkAttempts">
    {{-- Модальное окно предупреждения о незащищённом сейфе --}}
    <x-modal type="info" :show="$showUnprotectedModal" title="Сейф не защищён"
        description="Пароль для сейфа не установлен. Вы можете установить его в разделе Профиль." icon="lock"
        confirmMethod="closeModal" />
    @if ($isUnlocked)
        @if ($isLoading)
            <!-- Загрузчик после успешной валидации -->
            <div class="flex-1 flex items-center justify-center  min-h-[calc(100vh-4rem)]">
                <x-loader class="w-20 h-20 animate-spin text-indigo-600" />
            </div>
        @else
            <!-- Header Section -->
            <x-header :heading="$heading" :subheading="$subheading" showSearch />
            <!-- ControlPanel Section -->
            <x-notes-control-panel :perPage="$perPage" :filter="$filter" :sort="$sort" />
            <!-- Content Section -->
            <x-content-section :notes="$this->notes" :totalCount="$this->getTotalCount()" :search="$search" emptyIcon="lock"
                emptyTitle="Сейф пуст" emptyDescription="Создайте первую защищенную заметку" noResultsIcon="search-x"
                noResultsTitle="Совпадений не найдено" noResultsDescription="Попробуйте изменить поисковый запрос"
                noFilterResultsIcon="lock" noFilterResultsTitle="Совпадений нет"
                noFilterResultsDescription="Попробуйте изменить фильтры" />
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
                        <div class="mb-4 p-3 bg-red-100 border border-red-200 rounded-lg text-red-700 text-sm min-h-12">
                            {{ $errorMessage }}
                        </div>
                    @endif
                    <form wire:submit="verifyPassword" class="space-y-4">
                        <x-input-group type="password" wireModel="password" placeholder="Пароль" height="48px"
                            autofocus :error="$errorMessage ? true : false" />
                        <x-primary-button type="submit" class="w-full" height="h-12" :disabled="$isLoading">
                            @if ($isLoading)
                                <x-loader class="w-4 h-4 mr-2" />
                            @else
                                <i data-lucide="lock" class="w-4 h-4"></i>
                            @endif
                            Открыть сейф
                        </x-primary-button>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
