<div>
    <!-- Header Section -->
    <x-header :heading="$this->title" :section='$section' />
    <!-- Content Section -->
    <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 mb-6 py-6">
        <div class="bg-white rounded-xl shadow-md p-6">
            <form wire:submit.prevent="save" class="space-y-8">
                <!-- Название папки -->
                <div>
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
                        <label class="block text-lg font-medium text-gray-700 mb-2">
                            Иконка папки <span class="text-red-500">*</span>
                        </label>
                        <div class="rounded-lg border border-gray-200 overflow-hidden">
                            <div
                                class="folder-icons-scroll flex flex-wrap gap-3 p-3 bg-gray-50 overflow-y-auto max-h-[160px]">
                                @foreach ($this->icons as $key => $icon)
                                    @php
                                        $isUsedIcon = in_array($key, $this->usedIcons);
                                        $isSelectedIcon = $key === $this->icon;
                                    @endphp
                                    <button type="button"
                                        @if (!$isUsedIcon || $isSelectedIcon) wire:click="$set('icon', '{{ $key }}')" @endif
                                        wire:key="{{ $key }}" @if ($isUsedIcon && !$isSelectedIcon) disabled @endif
                                        class="flex items-center justify-center w-8 h-8 shrink-0 rounded-full bg-white border-2
                                            @if ($isSelectedIcon) border-white ring-2 ring-offset-2 ring-indigo-500
                                            @elseif($isUsedIcon)
                                                border-gray-200 opacity-40 cursor-not-allowed
                                            @else
                                                border-gray-300 hover:scale-110 @endif
                                            transition-all focus:outline-none
                                            @if (!$isUsedIcon || $isSelectedIcon) focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 @endif"
                                        title="{{ $icon['label'] }}{{ $isUsedIcon && !$isSelectedIcon ? ' (уже используется)' : '' }}"
                                        aria-label="{{ $icon['label'] }}">
                                        <i data-lucide="{{ $icon['icon'] }}"
                                            class="w-4 h-4 @if ($isUsedIcon && !$isSelectedIcon) text-gray-400 @else text-gray-700 @endif"></i>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        @error('icon')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Цвет папки -->
                    <div class="flex-1">
                        <label for="folder-color" class="block text-lg font-medium text-gray-700 mb-2">
                            Цвет папки <span class="text-red-500">*</span>
                        </label>
                        <div class="folder-color-picker h-[160px] rounded-lg border border-gray-200 overflow-hidden"
                            wire:ignore>
                            <input type="text" id="folder-color" data-coloris data-livewire-color
                                class="folder-color-input text-gray-700" placeholder="Выберите цвет"
                                value="{{ $this->color }}">
                        </div>
                        @error('color')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Кнопки действий -->
                <div class="flex flex-wrap items-center gap-3 justify-end">
                    <!-- Save Button -->
                    <x-button-save wire:click.prevent="save" target="save" />
                </div>
            </form>
        </div>
    </div>
    <!-- Delete Confirmation Modal -->
    <x-modal type="delete" :show="$confirmingDeletion" title="Удалить папку?"
        description="Папка будет перемещена в корзину. Вы сможете восстановить её позже." confirmMethod="deleteFolder"
        cancelMethod="closeModal" />
</div>
