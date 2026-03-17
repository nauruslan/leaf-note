<div>
    <!-- Header Section -->
    <header class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 ">
        <div class="bg-white rounded-b-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                        Редактирование списка
                    </h1>
                    <p class="text-sm text-gray-500 mt-0.5">Редактирование списка задач</p>
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
                        <div class="relative">
                            <select wire:model.live="folderId" wire:key="folder-{{ $folderId }}"
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
                                    wire:loading.attr="disabled"
                                    wire:key="color-{{ $key }}-{{ $this->color }}"
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
                    <!-- Delete Button -->
                    <button type="button" wire:click.prevent="openDeleteModal" wire:loading.attr="disabled"
                        class="bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white font-medium py-2.5 px-5 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                        Удалить
                    </button>

                    <!-- Save Button -->
                    <button type="button" wire:click="prepareAndSave" wire:loading.attr="disabled"
                        class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium py-2.5 px-5 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Сохранить
                    </button>

                    <!-- Cancel Button -->
                    <button type="button" wire:click.prevent="cancel" wire:loading.attr="disabled"
                        class="bg-gradient-to-r from-rose-500 to-red-600 hover:from-rose-600 hover:to-red-700 text-white font-medium py-2.5 px-5 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                        <i data-lucide="x" class="w-4 h-4"></i>
                        Отменить
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Section -->
    <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 pb-6">
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden min-h-[600px] flex flex-col">
            <!-- Скрытый input для хранения контента -->
            <input type="hidden" id="edit-checklist-content-input" value="">

            <!-- Note Title Input -->
            <div class="px-6 pt-6 pb-2 border-b border-gray-100">
                <input type="text" wire:model="title" placeholder="Название списка"
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

            <!-- Footer Info (ignored) -->
            <div wire:ignore>
                <div
                    class="px-6 py-3 border-t border-gray-200 bg-gray-50/50 flex justify-between items-center text-xs text-gray-500">
                    <div class="flex items-center gap-4">
                        <span>Создано: {{ $checklist?->created_at?->translatedFormat('d F Y') }}</span>
                        <span>•</span>
                        <span>Изменено: {{ $checklist?->updated_at?->translatedFormat('d F Y') }}</span>
                        <span>•</span>
                        <span data-task-count>0 задач</span>
                    </div>
                </div>
            </div>
        </div>


        <!-- Delete Confirmation Modal (ignored) -->
        <div wire:ignore>
            <div id="delete-modal" class="link-modal">
                <div class="link-modal-content">
                    <h3 class="link-modal-title">Удалить список?</h3>
                    <p class="text-sm text-gray-600 mt-2">Список будет перемещен в корзину</p>
                    <div class="link-modal-buttons">
                        <button type="button" class="link-modal-btn link-modal-btn-cancel" data-delete-action="cancel">
                            Отменить
                        </button>
                        <button type="button" class="link-modal-btn link-modal-btn-ok" data-delete-action="confirm"
                            style="background: #ef4444; border-color: #ef4444;">
                            Удалить
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @script
        <script>
            // Обработчик модального окна удаления
            document.addEventListener('click', function(e) {
                const deleteModal = document.getElementById('delete-modal');
                if (!deleteModal) return;

                if (e.target.closest('[data-delete-action="confirm"]')) {
                    deleteModal.classList.remove('active');
                    $wire.confirmDelete();
                }

                if (e.target.closest('[data-delete-action="cancel"]')) {
                    deleteModal.classList.remove('active');
                }
            });

            // Загрузка данных при редактировании
            Livewire.on('checklistLoaded', (data) => {
                let parsedContent = data && data.content ? data.content : data;
                if (typeof parsedContent === 'string') {
                    try {
                        parsedContent = JSON.parse(parsedContent);
                    } catch (e) {
                        parsedContent = '';
                    }
                }
            });
        </script>
    @endscript
</div>
