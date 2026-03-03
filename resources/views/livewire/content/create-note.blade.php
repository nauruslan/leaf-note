<!-- CreateNote Content (TipTap Editor) -->
<div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 pb-6">
    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden min-h-[600px] flex flex-col">
        <!-- TipTap Toolbar -->
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
                class="p-2 rounded-lg hover:bg-gray-200 text-gray-600 hover:text-gray-900 transition-colors"
                title="Выделить текст (маркер)">
                <i data-lucide="highlighter" class="w-4 h-4"></i>
            </button>
            <div class="w-px h-6 bg-gray-300 mx-2"></div>
            <!-- Headings -->
            <button type="button" data-editor-action="heading1"
                class="p-2 rounded-lg hover:bg-gray-200 text-gray-600 hover:text-gray-900 transition-colors"
                title="Заголовок">
                <span class="font-bold text-sm">H1</span>
            </button>
            <button type="button" data-editor-action="heading2"
                class="p-2 rounded-lg hover:bg-gray-200 text-gray-600 hover:text-gray-900 transition-colors"
                title="Подзаголовок">
                <span class="font-bold text-sm">H2</span>
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
            <button type="button" data-editor-action="code"
                class="p-2 rounded-lg hover:bg-gray-200 text-gray-600 hover:text-gray-900 transition-colors"
                title="Код">
                <i data-lucide="code" class="w-4 h-4"></i>
            </button>
            <div class="w-px h-6 bg-gray-300 mx-2"></div>
            <button type="button" data-editor-action="table"
                class="p-2 rounded-lg hover:bg-gray-200 text-gray-600 hover:text-gray-900 transition-colors"
                title="Таблица">
                <i data-lucide="table" class="w-4 h-4"></i>
            </button>
            <!-- Table Controls (скрыты по умолчанию) -->
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
        <!-- Note Title Input -->
        <div class="px-6 pt-6 pb-2 border-b border-gray-100">
            <input type="text" wire:model="title" placeholder="Название заметки"
                class="p-0 w-full text-2xl font-bold text-gray-900 placeholder-gray-400 border-none focus:outline-none focus:ring-0 bg-transparent">
        </div>
        <!-- TipTap Editor Content Area -->
        <div class="flex-grow p-6">
            <div id="editor" wire:ignore.self
                class="prose prose-indigo max-w-none focus:outline-none min-h-[400px] text-gray-700">
            </div>
        </div>

        <!-- Footer Info -->
        <div
            class="px-6 py-3 border-t border-gray-200 bg-gray-50/50 flex justify-between items-center text-xs text-gray-500">
            <div class="flex items-center gap-4">
                <span>Создано: только что</span>
                <span>•</span>
                <span data-word-count>0 слов</span>
            </div>
            <div class="flex items-center gap-2">
                <i data-lucide="cloud" class="w-3 h-3"></i>
                <span>Автосохранение включено</span>
            </div>
        </div>
    </div>
    <!-- Link Modal -->
    <div id="link-modal" class="link-modal">
        <div class="link-modal-content">
            <h3 class="link-modal-title">Введите ссылку</h3>
            <input type="url" id="link-input" class="link-modal-input" placeholder="https://example.com"
                autocomplete="off">
            <div class="link-modal-buttons">
                <button type="button" class="link-modal-btn link-modal-btn-ok" data-link-action="ok">
                    ОК
                </button>
                <button type="button" class="link-modal-btn link-modal-btn-cancel" data-link-action="cancel">
                    Отменить
                </button>
            </div>
        </div>
    </div>
</div>
