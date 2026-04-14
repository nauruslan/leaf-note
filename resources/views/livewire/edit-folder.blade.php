<div>
    <!-- Header Section -->
    <x-header :heading="$heading" :subheading="$this->title" />
    <!-- Content Section -->
    <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 mb-6 py-6">
        <div class="bg-white rounded-xl shadow-md p-6">
            <form wire:submit.prevent="save" class="space-y-8">
                <!-- Название папки -->
                <div class="h-[70px]">
                    <label for="folder-title" class="block text-lg font-medium text-gray-700 mb-1.5">
                        Название папки <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="folder-title" wire:model="title"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow"
                        placeholder="Введите название папки">
                    @error('title')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Иконка и Цвет папки на одной строке -->
                <div class="flex flex-col lg:flex-row gap-6">
                    <!-- Иконка папки -->
                    <div class="flex-1">
                        <label class="block text-lg font-medium text-gray-700 mb-2">Иконка папки</label>
                        <div class="flex flex-wrap gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                            @foreach ($this->icons as $key => $icon)
                                <button type="button" wire:click="$set('icon', '{{ $key }}')"
                                    wire:key="{{ $key }}"
                                    class="flex items-center justify-center w-8 h-8 shrink-0 rounded-full bg-white border-2 {{ $key === $this->icon ? 'border-white ring-2 ring-offset-2 ring-indigo-500' : 'border-gray-300' }} hover:scale-110 transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                    title="{{ $icon['label'] }}" aria-label="{{ $icon['label'] }}">
                                    <i data-lucide="{{ $icon['icon'] }}" class="w-4 h-4 text-gray-700"></i>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Цвет папки -->
                    <div class="flex-1">
                        <label class="block text-lg font-medium text-gray-700 mb-2">Цвет папки</label>
                        <div class="flex flex-wrap gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                            @foreach ($this->colors as $key => $color)
                                <button type="button" wire:click="$set('color', '{{ $key }}')"
                                    wire:key="{{ $key }}"
                                    style="background-color: {{ $color['hex'] }}; border-color: {{ $key === $this->color ? '#FFFFFF' : $color['hex'] }};"
                                    class="relative w-8 h-8 shrink-0 rounded-full border-2 {{ $key === $this->color ? 'ring-2 ring-offset-2 ring-indigo-500' : '' }} hover:scale-110 transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 ring-indigo-500"
                                    title="{{ $color['label'] }}" aria-label="{{ $color['label'] }}">
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Кнопки действий -->
                <div class="flex flex-wrap items-center gap-3 justify-end">
                    <!-- Save Button -->
                    <x-button-save wire:click.prevent="save" wire:loading.attr="disabled" />
                    <!-- Delete Button -->
                    <x-button-delete wire:click.prevent="confirmDeletion" wire:loading.attr="disabled" />
                </div>
            </form>
        </div>
    </div>
    <!-- Delete Confirmation Modal -->
    <x-modal type="delete" :show="$confirmingDeletion" title="Удалить папку?"
        description="Папка будет перемещена в корзину. Вы сможете восстановить её позже." confirmMethod="deleteFolder"
        cancelMethod="closeModal" />
</div>
