<div>
    <!-- Header Section -->
    <header class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-b-xl shadow-md p-5">
            <h1 class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                Редактирование папки
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $folder->title }}</p>
        </div>
    </header>

    <!-- Content Section -->
    <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 mb-6 py-6">
        <div class="bg-white rounded-xl shadow-md p-6">
            <form wire:submit.prevent="createFolder" class="space-y-8">
                <!-- Название папки -->
                <div class="h-[70px]">
                    <label for="folder-title" class="block text-lg font-medium text-gray-700 mb-1.5">
                        Название папки <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="folder-title" wire:model="title" autofocus
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow"
                        placeholder="Введите название папки">
                    @error('title')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Иконка папки -->
                <div>
                    <label class="block text-lg font-medium text-gray-700 mb-2">Иконка папки</label>
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200 h-[70px]">
                        @foreach ($this->icons as $key => $icon)
                            <button type="button" wire:click="$set('icon', '{{ $key }}')"
                                wire:key="{{ $key }}"
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-white border-2 {{ $key === $this->icon ? 'border-white ring-2 ring-offset-2 ring-indigo-500' : 'border-gray-300' }} hover:scale-110 transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                title="{{ $icon['label'] }}" aria-label="{{ $icon['label'] }}">
                                <i data-lucide="{{ $icon['icon'] }}" class="w-4 h-4 text-gray-700"></i>
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Цвет папки -->
                <div>
                    <label class="block text-lg font-medium text-gray-700 mb-2">Цвет папки</label>
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200 h-[70px]">
                        @foreach ($this->colors as $key => $color)
                            <button type="button" wire:click="$set('color', '{{ $key }}')"
                                wire:key="{{ $key }}"
                                class="relative w-8 h-8 rounded-full {{ $color['bg'] }} border-2 {{ $key === $this->color ? 'border-white ring-2 ring-offset-2 ring-indigo-500' : $color['border'] }} hover:scale-110 transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 ring-indigo-500"
                                title="{{ $color['label'] }}" aria-label="{{ $color['label'] }}">
                                <!-- Leaf Component -->
                                <x-leaf class="w-5 h-5 absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2"
                                    :fill="$key === 'white' ? '#000000' : '#ffffff'" />
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Кнопки действий -->
                <div class="flex flex-wrap items-center gap-3 justify-end">
                    <!-- Save Button -->
                    <button type="button" wire:click.prevent="save" wire:loading.attr="disabled"
                        class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium py-2.5 px-5 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" data-lucide="save" aria-hidden="true"
                            class="lucide lucide-save w-4 h-4">
                            <path
                                d="M15.2 3a2 2 0 0 1 1.4.6l3.8 3.8a2 2 0 0 1 .6 1.4V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z">
                            </path>
                            <path d="M17 21v-7a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v7"></path>
                            <path d="M7 3v4a1 1 0 0 0 1 1h7"></path>
                        </svg>
                        Создать
                    </button>

                    <!-- Cancel Button -->
                    <button type="button" wire:click.prevent="cancel" wire:loading.attr="disabled"
                        class="bg-gradient-to-r from-rose-500 to-red-600 hover:from-rose-600 hover:to-red-700 text-white font-medium py-2.5 px-5 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" data-lucide="x" aria-hidden="true" class="lucide lucide-x w-4 h-4">
                            <path d="M18 6 6 18"></path>
                            <path d="m6 6 12 12"></path>
                        </svg>
                        Отмена
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
