<div>
    <!-- Header Section -->
    <header class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 ">
        <div class="bg-white rounded-b-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                        Редактирование заметки
                    </h1>
                    <p class="text-sm text-gray-500 mt-0.5">Редактирование заметки</p>
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

                    <!-- Favorite -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Избранное:</span>
                        <x-star :active="$is_favorite" wire:click="toggleFavorite" size="30px" />
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
                        Отменить
                    </button>
                    <!-- Delete Button -->
                    <button type="button" wire:click.prevent="openDeleteModal" wire:loading.attr="disabled"
                        class="bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white font-medium py-2.5 px-5 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                        Удалить
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Section -->
    <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 pb-6">
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden min-h-[600px] flex flex-col">
            <!-- TipTap Toolbar (ignored) -->
            <div wire:ignore>
                <!-- Скрытый input для загрузки изображений -->
                <input type="file" id="note-view-image-upload-input" accept="image/*" style="display:none">

                <div class="px-6 py-3 border-b border-gray-200 bg-gray-50/50 flex flex-wrap items-center gap-1">
                    <!-- Text Formatting -->
                    <button type="button" data-editor-action="bold"
                        class="p-2 rounded-lg hover:bg-gray-200 text-gray-600 hover:text-gray-900 transition-colors"
                        title="Жирный">
                        <i data-lucide="bold" class="w-4 h-4"></i>
                    </button>
                    <button type="button" data-editor-action="italic"
                        class="p-2 rounded-lg hover:bg-gray-200 text-gray-600 hover:text-gray-900 transition-colors"
                        title="Курсив">
                        <i data-lucide="italic" class="w-4 h-4"></i>
                    </button>
                    <button type="button" data-editor-action="underline"
                        class="p-2 rounded-lg hover:bg-gray-200 text-gray-600 hover:text-gray-900 transition-colors"
                        title="Подчеркнутый">
                        <i data-lucide="underline" class="w-4 h-4"></i>
                    </button>
                    <button type="button" data-editor-action="strike"
                        class="p-2 rounded-lg hover:bg-gray-200 text-gray-600 hover:text-gray-900 transition-colors"
                        title="Зачеркнутый">
                        <i data-lucide="strikethrough" class="w-4 h-4"></i>
                    </button>
                    <!-- Color Picker Button -->
                    <button type="button" data-editor-action="color"
                        class="p-2 rounded-lg hover:bg-gray-200 text-gray-600 hover:text-gray-900 transition-colors relative"
                        title="Цвет текста">
                        <i data-lucide="palette" class="w-4 h-4"></i>
                    </button>
                    <!-- Highlight Button (новый) -->
                    <button type="button" data-editor-action="highlight"
                        class="p-2 rounded-lg hover:bg-gray-200 text-gray-600 hover:text-gray-900 transition-colors relative"
                        title="Выделить текст (маркер)">
                        <i data-lucide="highlighter" class="w-4 h-4"></i>
                    </button>
                    <div class="w-px h-6 bg-gray-300 mx-2"></div>
                    <!-- Headings -->
                    <button type="button" data-editor-action="heading1"
                        class="p-2 rounded-lg hover:bg-gray-200 text-gray-600 hover:text-gray-900 transition-colors relative"
                        title="Заголовок">
                        <span class="font-bold text-[16px] w-4 h-4 flex items-center justify-center">H1</span>
                    </button>
                    <button type="button" data-editor-action="heading2"
                        class="p-2 rounded-lg hover:bg-gray-200 text-gray-600 hover:text-gray-900 transition-colors relative"
                        title="Подзаголовок">
                        <span class="font-bold text-[16px] w-4 h-4 flex items-center justify-center">H2</span>
                    </button>
                    <div class="w-px h-6 bg-gray-300 mx-2"></div>
                    <!-- Lists -->
                    <button type="button" data-editor-action="bulletList"
                        class="p-2 rounded-lg hover:bg-gray-200 text-gray-600 hover:text-gray-900 transition-colors"
                        title="Маркированный список">
                        <i data-lucide="list" class="w-4 h-4"></i>
                    </button>
                    <button type="button" data-editor-action="orderedList"
                        class="p-2 rounded-lg hover:bg-gray-200 text-gray-600 hover:text-gray-900 transition-colors"
                        title="Нумерованный список">
                        <i data-lucide="list-ordered" class="w-4 h-4"></i>
                    </button>
                    <button type="button" data-editor-action="taskList"
                        class="p-2 rounded-lg hover:bg-gray-200 text-gray-600 hover:text-gray-900 transition-colors"
                        title="Задача">
                        <i data-lucide="list-checks" class="w-4 h-4"></i>
                    </button>
                    <div class="w-px h-6 bg-gray-300 mx-2"></div>
                    <!-- Alignment Buttons -->
                    <button type="button" data-editor-action="alignLeft"
                        class="p-2 rounded-lg hover:bg-gray-200 text-gray-600 hover:text-gray-900 transition-colors"
                        title="Выровнять по левому краю">
                        <i data-lucide="align-left" class="w-4 h-4"></i>
                    </button>
                    <button type="button" data-editor-action="alignCenter"
                        class="p-2 rounded-lg hover:bg-gray-200 text-gray-600 hover:text-gray-900 transition-colors"
                        title="По центру">
                        <i data-lucide="align-center" class="w-4 h-4"></i>
                    </button>
                    <button type="button" data-editor-action="alignRight"
                        class="p-2 rounded-lg hover:bg-gray-200 text-gray-600 hover:text-gray-900 transition-colors"
                        title="По правому краю">
                        <i data-lucide="align-right" class="w-4 h-4"></i>
                    </button>
                    <div class="w-px h-6 bg-gray-300 mx-2"></div>
                    <!-- Insert -->
                    <button type="button" data-editor-action="link"
                        class="p-2 rounded-lg hover:bg-gray-200 text-gray-600 hover:text-gray-900 transition-colors"
                        title="Ссылка">
                        <i data-lucide="link" class="w-4 h-4"></i>
                    </button>
                    <button type="button" data-editor-action="image"
                        class="p-2 rounded-lg hover:bg-gray-200 text-gray-600 hover:text-gray-900 transition-colors"
                        title="Изображение">
                        <i data-lucide="image" class="w-4 h-4"></i>
                    </button>
                    <div class="w-px h-6 bg-gray-300 mx-2"></div>
                    <button type="button" data-editor-action="table"
                        class="p-2 rounded-lg hover:bg-gray-200 text-gray-600 hover:text-gray-900 transition-colors"
                        title="Таблица">
                        <i data-lucide="table" class="w-4 h-4"></i>
                    </button>
                    <!-- Table Controls -->
                    <div class="flex items-center gap-1 hidden table-controls">
                        <button type="button" data-editor-action="addRow"
                            class="p-1.5 rounded hover:bg-gray-200 text-gray-600 hover:text-gray-900 transition-colors opacity-50 cursor-not-allowed"
                            title="Добавить строку" disabled>
                            <i data-lucide="rows" class="w-3.5 h-3.5"></i>
                        </button>
                        <button type="button" data-editor-action="addCol"
                            class="p-1.5 rounded hover:bg-gray-200 text-gray-600 hover:text-gray-900 transition-colors opacity-50 cursor-not-allowed"
                            title="Добавить столбец" disabled>
                            <i data-lucide="columns" class="w-3.5 h-3.5"></i>
                        </button>
                        <button type="button" data-editor-action="deleteRow"
                            class="p-1.5 rounded hover:bg-gray-200 text-gray-600 hover:text-gray-900 transition-colors opacity-50 cursor-not-allowed"
                            title="Удалить строку" disabled>
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        </button>
                        <button type="button" data-editor-action="deleteCol"
                            class="p-1.5 rounded hover:bg-gray-200 text-gray-600 hover:text-gray-900 transition-colors opacity-50 cursor-not-allowed"
                            title="Удалить столбец" disabled>
                            <i data-lucide="trash" class="w-3.5 h-3.5"></i>
                        </button>
                        <div class="w-px h-4 bg-gray-300 mx-1"></div>
                        <button type="button" data-editor-action="mergeCells"
                            class="p-1.5 rounded hover:bg-gray-200 text-gray-600 hover:text-gray-900 transition-colors opacity-50 cursor-not-allowed"
                            title="Объединить ячейки" disabled>
                            <i data-lucide="combine" class="w-3.5 h-3.5"></i>
                        </button>
                        <button type="button" data-editor-action="splitCell"
                            class="p-1.5 rounded hover:bg-gray-200 text-gray-600 hover:text-gray-900 transition-colors opacity-50 cursor-not-allowed"
                            title="Разделить ячейку" disabled>
                            <i data-lucide="split" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                    <div class="w-px h-6 bg-gray-300 mx-2"></div>
                    <!-- Undo/Redo -->
                    <button type="button" data-editor-action="undo"
                        class="p-2 rounded-lg hover:bg-gray-200 text-gray-600 hover:text-gray-900 transition-colors"
                        title="Отменить">
                        <i data-lucide="undo" class="w-4 h-4"></i>
                    </button>
                    <button type="button" data-editor-action="redo"
                        class="p-2 rounded-lg hover:bg-gray-200 text-gray-600 hover:text-gray-900 transition-colors"
                        title="Повторить">
                        <i data-lucide="redo" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
            <!-- Note Title Input -->
            <div class="px-6 pt-6 pb-2 border-b border-gray-100">
                <input type="text" wire:model="title" placeholder="Название заметки"
                    class="p-0 w-full text-2xl font-bold text-gray-900 placeholder-gray-400 border-none focus:outline-none focus:ring-0 bg-transparent">
            </div>
            <!-- TipTap Editor Content Area (ignored) -->
            <div wire:ignore>
                <div class="flex-grow p-6">
                    <div id="note-view-editor" data-content="{{ json_encode($content) }}"
                        class="prose prose-indigo max-w-none focus:outline-none min-h-[400px] text-gray-700">
                    </div>
                </div>
            </div>

            <!-- Footer Info (ignored) -->
            <div wire:ignore>
                <div
                    class="px-6 py-3 border-t border-gray-200 bg-gray-50/50 flex justify-between items-center text-xs text-gray-500">
                    <div class="flex items-center gap-4">
                        <span>Создано: {{ $note?->created_at?->translatedFormat('d F Y') }}</span>
                        <span>•</span>
                        <span>Изменено: {{ $note?->updated_at?->translatedFormat('d F Y') }}</span>
                        <span>•</span>
                        <span data-char-count>0 символов</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- Link Modal (ignored) -->
        <div wire:ignore>
            <div id="link-modal" class="link-modal">
                <div class="link-modal-content">
                    <h3 class="link-modal-title">Введите ссылку</h3>
                    <input type="url" id="link-input" class="link-modal-input" placeholder="https://example.com"
                        autocomplete="off">
                    <div class="link-modal-buttons">
                        <button type="button" class="link-modal-btn link-modal-btn-ok" data-link-action="ok">
                            ОК
                        </button>
                        <button type="button" class="link-modal-btn link-modal-btn-cancel"
                            data-link-action="cancel">
                            Отменить
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal (ignored) -->
        <div wire:ignore>
            <div id="delete-modal" class="link-modal">
                <div class="link-modal-content">
                    <h3 class="link-modal-title">Удалить заметку?</h3>
                    <p class="text-sm text-gray-600 mt-2">Заметка будет перемещена в корзину</p>
                    <div class="link-modal-buttons">
                        <button type="button" class="link-modal-btn link-modal-btn-cancel"
                            data-delete-action="cancel">
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
            Livewire.on('restoreNoteOriginalState', () => {
                document.dispatchEvent(new CustomEvent('restore-note-original-state'));
            });

            document.addEventListener('click', function(e) {
                const deleteModal = document.getElementById('delete-modal');

                if (e.target.closest('[data-delete-action="confirm"]')) {
                    deleteModal.classList.remove('active');
                    @this.confirmDelete();
                }

                if (e.target.closest('[data-delete-action="cancel"]')) {
                    deleteModal.classList.remove('active');
                }
            });
        </script>
    @endscript
</div>
