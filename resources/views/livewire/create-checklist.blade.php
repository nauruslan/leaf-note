<div>
    <!-- Header Section -->
    <header class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 ">
        <div class="bg-white rounded-b-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                        Создание списка задач
                    </h1>
                    <p class="text-sm text-gray-500 mt-0.5">Создайте новый список задач</p>
                </div>
            </div>
        </div>
    </header>

    <!-- ControlPanel Section -->
    <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="bg-white rounded-xl shadow-md p-5 border border-gray-100">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">

                <!-- Left Block: Main Settings -->
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Folder Selection -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Папка:</span>
                        <x-dropdown :options="$this->folders->map(fn($f) => ['value' => $f->id, 'text' => $f->title])->toArray()" :safes="$this->safes->toArray()" selected="{{ $folderId ?? $safeId }}"
                            wireModel="folderId" live width="150px" />
                    </div>

                    <!-- Favorite -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Избранное:</span>
                        <x-star :active="$is_favorite" size="30px" wire:click.debounce.500ms="toggleFavorite" />
                    </div>
                </div>

                <!-- Right Block: Actions -->
                <div class="flex flex-wrap items-center gap-3 justify-end">

                    <!-- Save Button -->
                    <x-button-save wire:click.prevent="saveWithLocation" wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-not-allowed">
                        <span wire:loading>Сохранение...</span>
                        <span wire:loading.remove class="flex items-center gap-2">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            Сохранить
                        </span>
                    </x-button-save>

                    <!-- Cancel Button -->
                    <x-button-cancel wire:click.prevent="cancel" wire:loading.attr="disabled">
                        <i data-lucide="x" class="w-4 h-4"></i>
                        Отменить
                    </x-button-cancel>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Section -->
    <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 pb-6">
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden min-h-[600px] flex flex-col">
            <!-- Note Title Input -->
            <div class="px-6 pt-6 pb-2 border-b border-gray-100">
                <input type="text" wire:model.live.debounce.300ms="title" placeholder="Название списка"
                    class="p-0 w-full text-2xl font-bold text-gray-900 placeholder-gray-400 border-none focus:outline-none focus:ring-0 bg-transparent">
            </div>


            <!-- Progress -->
            <div wire:ignore>
                <div id="checklist-progress-bar" class="px-6 py-4 border-b border-gray-200 flex justify-center">
                    <!-- Progress bar will be initialized by JS -->
                </div>
            </div>


            <!-- Checklist Editor -->
            <div wire:ignore>
                <div class="flex-grow p-6">
                    <div id="create-checklist-editor"
                        class="checklist-editor prose prose-indigo max-w-none focus:outline-none min-h-[400px] text-gray-700">
                    </div>
                </div>
            </div>

            <!-- Hidden input for content synchronization -->
            <input type="hidden" wire:model.live.debounce.500ms="content" id="checklist-content-input">

            <!-- Footer Info (ignored) -->
            <div wire:ignore>
                <div
                    class="px-6 py-3 border-t border-gray-200 bg-gray-50/50 flex justify-between items-center text-xs text-gray-500">
                    <div class="flex items-center gap-4">
                        <span>Создано: только что</span>
                        <span>•</span>
                        <span>Изменено: только что</span>
                        <span>•</span>
                        <span data-task-count>0 задач</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @script
        <script>
            document.addEventListener('update-safe-id', (e) => {
                Livewire.dispatch('updateSafeId', {
                    id: e.detail.id
                });
            });
        </script>
    @endscript
</div>
