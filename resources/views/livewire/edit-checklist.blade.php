<div>
    <!-- Header Section -->
    <x-header :heading="$heading" :subheading="$this->checklist->title" />
    <!-- ControlPanel Section -->
    <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="bg-white rounded-xl shadow-md p-5 border border-gray-100">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <!-- Left Block: Main Settings -->
                <div class="flex flex-wrap items-center gap-3 justify-end">
                    <!-- Back Button -->
                    <x-button-back wire:click.prevent="back" wire:loading.attr="disabled" />
                    <!-- Delete Button -->
                    <x-button-delete wire:click.prevent="toggleDeleteModal" wire:loading.attr="disabled" />
                </div>
                <!-- Right Block: Actions -->
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Folder Selection -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Папка:</span>
                        <x-dropdown :options="$this->folders->map(fn($f) => ['value' => $f->id, 'text' => $f->title])->toArray()" :safes="$this->safes" selected="{{ $folderId ?? $safeId }}"
                            wireModel="folderId" live width="150px" />
                    </div>
                    <!-- Favorite -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Избранное:</span>
                        <x-dropdown :options="[['value' => '1', 'text' => 'Да'], ['value' => '0', 'text' => 'Нет']]" selected="{{ $is_favorite ? '1' : '0' }}" wireModel="is_favorite"
                            live width="80px" />
                    </div>
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
                    <div id="edit-checklist-editor"
                        class="checklist-editor prose prose-indigo max-w-none focus:outline-none min-h-[400px] text-gray-700">
                    </div>
                </div>
            </div>

            <!-- Hidden input for content synchronization -->
            <input type="hidden" wire:model.live.debounce.500ms="content" id="checklist-content-input">

            <!-- Footer Info -->
            <div
                class="px-6 py-3 border-t border-gray-200 bg-gray-50/50 flex justify-between items-center text-xs text-gray-500">
                <div wire:ignore class="flex items-center gap-4">
                    <span>Создано: {{ $this->checklist?->created_at?->translatedFormat('d F Y') }}</span>
                    <span>•</span>
                    <span>Изменено: {{ $this->checklist?->updated_at?->translatedFormat('d F Y') }}</span>
                    <span>•</span>
                    <span data-task-count>0 задач</span>
                </div>
                <div class="flex items-center" id="autosave-container">
                    <div id="autosave-spinner" class="flex items-center" wire:loading
                        wire:target="title,content,folderId,safeId">
                        <svg class="animate-spin h-3 w-3 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>


        <!-- Delete Confirmation Modal -->
        <x-modal-delete :confirmingDeletion="$confirmingDeletion" title="Удалить список?" description="Список будет перемещен в корзину"
            closeMethod="toggleDeleteModal" deleteMethod="delete" />
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
