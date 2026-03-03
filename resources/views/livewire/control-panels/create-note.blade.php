<!-- CreateNote ControlPanel -->
<div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="bg-white rounded-xl shadow-md p-5 border border-gray-100">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">

            <!-- Left Block: Main Settings -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Folder Selection -->
                <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Папка:</span>
                    <div class="relative">
                        <select wire:model.live="folderId"
                            class="appearance-none bg-gray-50 border border-gray-300 text-gray-700 py-2 pl-3 pr-8 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm min-w-[150px] hover:bg-gray-100 transition-colors">
                            <option value="">Выберите папку</option>
                            @foreach ($folders as $folder)
                                <option value="{{ $folder->id }}">{{ $folder->title }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                            <i data-lucide="chevron-down" class="w-3 h-3 text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <!-- Color Picker -->
                <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Цвет:</span>
                    <div class="flex items-center gap-1.5">
                        @foreach ($this->colors as $key => $color)
                            <button type="button" wire:click="$set('color', '{{ $key }}')"
                                wire:key="{{ $key }}"
                                class="relative w-8 h-8 rounded-full {{ $color['bg'] }} border-2 {{ $key === $this->color ? 'border-white ring-2 ring-offset-2 ' . $color['ring'] : $color['border'] }} hover:scale-110 transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $color['ring'] }}"
                                title="{{ $color['label'] }}" aria-label="{{ $color['label'] }}">
                                <!-- Leaf Component -->
                                <x-leaf class="w-5 h-5 absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2"
                                    :fill="$key === 'white' ? '#000000' : '#ffffff'" />
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Right Block: Actions -->
            <div class="flex flex-wrap items-center gap-3 justify-end">
                <!-- Save Button -->
                <button type="button" wire:click.prevent="save" wire:loading.attr="disabled"
                    class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium py-2.5 px-5 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Сохранить
                </button>

                <!-- Cancel Button -->
                <button type="button" wire:click.prevent="cancel" wire:loading.attr="disabled"
                    class="bg-gradient-to-r from-rose-500 to-red-600 hover:from-rose-600 hover:to-red-700 text-white font-medium py-2.5 px-5 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                    <i data-lucide="x" class="w-4 h-4"></i>
                    Отмена
                </button>
            </div>
        </div>
    </div>
</div>
