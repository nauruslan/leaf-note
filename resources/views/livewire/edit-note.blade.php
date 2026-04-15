<div>
    <!-- Header Section -->
    <x-header :heading="$title" :section='$section' />
    <!-- ControlPanel Section -->
    <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="bg-white rounded-xl shadow-md p-5 border border-gray-100">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <!-- Actions Block: Now starts from the left -->
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Folder Selection -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Папка:</span>
                        <x-dropdown :options="$this->folders->map(fn($f) => ['value' => $f->id, 'text' => $f->title])->toArray()" :safes="$this->safes->toArray()" :archives="$this->archives->toArray()"
                            selected="{{ $dropdownValue ?? ($folderId ?? (($safeId ? 'safe_' . $safeId : null) ?? ($archiveId ? 'archive_' . $archiveId : null))) }}"
                            wireModel="dropdownValue" live width="150px" />
                    </div>
                    <!-- Favorite -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Избранное:</span>
                        <x-dropdown :options="[['value' => '1', 'text' => 'Да'], ['value' => '0', 'text' => 'Нет']]" selected="{{ $is_favorite ? '1' : '0' }}" wireModel="is_favorite"
                            live width="80px" data-dropdown-favorite />
                    </div>
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
                <input type="text" wire:model.live.debounce.300ms="title" placeholder="Название заметки"
                    class="p-0 w-full text-2xl font-bold text-gray-900 placeholder-gray-400 border-none focus:outline-none focus:ring-0 bg-transparent">
            </div>
            <!-- TipTap Editor Content Area (ignored) -->
            <div wire:ignore>
                <div class="flex-grow p-6">
                    @php
                        $editorContent = [];
                        if ($content) {
                            if (is_array($content)) {
                                $editorContent = $content;
                            } elseif (is_string($content)) {
                                $decoded = json_decode($content, true);
                                if (is_array($decoded)) {
                                    $editorContent = $decoded;
                                }
                            }
                        }
                    @endphp
                    <div id="note-view-editor" data-content='@json($editorContent)'
                        class="prose prose-indigo max-w-none focus:outline-none min-h-[400px] text-gray-700">
                    </div>
                </div>
            </div>
            <!-- Hidden input for Livewire content synchronization -->
            <input type="hidden" id="note-view-content-input" wire:model.live.debounce.300ms="content">
            <!-- Footer Info -->
            <div
                class="px-6 py-3 border-t border-gray-200 bg-gray-50/50 flex justify-between items-center text-xs text-gray-500">
                <div wire:ignore class="flex items-center gap-4">
                    <span>Создано: {{ $this->note?->created_at?->translatedFormat('d F Y') }}</span>
                    <span>•</span>
                    <span>Изменено: {{ $this->note?->updated_at?->translatedFormat('d F Y') }}</span>
                    <span>•</span>
                    <span data-char-count>0 символов</span>
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
        <!-- Delete Confirmation Modal -->
        <x-modal type="delete" :show="$confirmingDeletion" title="Удалить заметку?"
            description="Заметка будет перемещена в корзину" confirmMethod="confirmDelete"
            cancelMethod="closeModal" />
    </div>

    @script
        <script>
            Livewire.on('restoreNoteOriginalState', () => {
                document.dispatchEvent(new CustomEvent('restore-note-original-state'));
            });
            document.addEventListener('update-safe-id', (e) => {
                Livewire.dispatch('updateSafeId', {
                    id: e.detail.id
                });
            });
            document.addEventListener('update-archive-id', (e) => {
                Livewire.dispatch('updateArchiveId', {
                    id: e.detail.id
                });
            });
        </script>
    @endscript
</div>
