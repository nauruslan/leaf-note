import { restoreImage, softDeleteImage } from './editor-helpers';
import { initNoteEditor, sendContentToLivewire } from './note';

/**
 * Класс для инициализации и управления редактором заметок в режиме просмотра
 */
export default class NoteEdit {
    constructor() {
        this.initialized = false;
        this.observer = null;
        this.editorInstance = null;
        this.originalImagePaths = [];
        this.newImagePaths = [];
        this.originalContent = null;
        this.previousImagePaths = new Set();
    }

    /**
     * Извлечь пути изображений из контента редактора
     */
    extractImagePathsFromContent(content) {
        const paths = new Set();
        if (!content || !content.content) return paths;

        function traverse(node) {
            if (!node) return;
            if (node.type === 'image' && node.attrs?.path) {
                paths.add(node.attrs.path);
            }
            if (node.content) {
                node.content.forEach(traverse);
            }
        }

        traverse(content);
        return paths;
    }

    /**
     * Сравнить текущие и предыдущие пути изображений,
     * выполнить мягкое удаление/восстановление при undo/redo
     */
    syncImageState(currentContent) {
        const currentPaths = this.extractImagePathsFromContent(currentContent);
        const previousPaths = this.previousImagePaths;

        // Изображения, которые были удалены из контента
        for (const path of previousPaths) {
            if (!currentPaths.has(path)) {
                softDeleteImage(path);
            }
        }

        // Изображения, которые появились в контенте
        for (const path of currentPaths) {
            if (!previousPaths.has(path)) {
                restoreImage(path);
            }
        }

        // Обновляем предыдущее состояние
        this.previousImagePaths = currentPaths;
    }

    /**
     * Инициализация модуля
     */
    init() {
        if (this.initialized) return;

        this.initEditor();
        this.setupEditorObserver();
        this.setupLivewireListeners();
        this.initialized = true;
    }

    /**
     * Переинициализация модуля
     */
    reinit() {
        this.destroy();
        this.init();
    }

    /**
     * Инициализация редактора
     */
    initEditor() {
        const editorElement = document.querySelector('#note-view-editor');
        if (!editorElement) return;

        // Уничтожаем существующий редактор
        if (editorElement._editor) {
            editorElement._editor.destroy();
            editorElement._editor = null;
        }

        let content = editorElement.dataset.content;
        if (content) {
            try {
                content = JSON.parse(content);
                this.originalContent = content;
                this.originalImagePaths = Array.from(this.extractImagePathsFromContent(content));
                this.previousImagePaths = this.extractImagePathsFromContent(content);
            } catch {
                content = '';
            }
        } else {
            content = '';
        }

        this.createEditor(content);
    }

    /**
     * Создание экземпляра редактора
     */
    createEditor(content) {
        this.originalImagePaths = [];
        this.newImagePaths = [];
        this.previousImagePaths = new Set();
        this.originalContent = content;

        // Уничтожаем существующий редактор
        if (this.editorInstance) {
            this.editorInstance.destroy();
            this.editorInstance = null;
        }

        const editor = initNoteEditor({
            elementId: 'note-view-editor',
            content: content,
            placeholder: 'Начните вводить текст заметки...',
            type: 'note-view',
            onImageUploaded: (imagePath) => {
                if (imagePath && !this.newImagePaths.includes(imagePath)) {
                    this.newImagePaths.push(imagePath);
                }
            },
            onUpdate: (editor) => {
                // Сохраняем последние данные
                const json = editor.getJSON();

                // Синхронизируем состояние изображений (мягкое удаление/восстановление)
                this.syncImageState(json);

                // Обновляем скрытый input для синхронизации с Livewire
                const contentInput = document.getElementById('note-view-content-input');
                if (contentInput) {
                    contentInput.value = JSON.stringify(json);

                    // Триггерим событие input для Livewire с debounce
                    clearTimeout(window.noteViewUpdateTimeout);
                    const valueToSend = JSON.stringify(json);
                    window.noteViewUpdateTimeout = setTimeout(() => {
                        const editorElement = document.getElementById('note-view-editor');
                        if (editorElement && editorElement._editor) {
                            contentInput.value = valueToSend;
                            contentInput.dispatchEvent(
                                new window.Event('input', { bubbles: true }),
                            );
                            this.showAutosaveIndicator();
                        }
                    }, 300);
                }
            },
        });

        this.editorInstance = editor;

        // Инициализируем предыдущее состояние изображений
        this.previousImagePaths = this.extractImagePathsFromContent(content);

        return editor;
    }

    /**
     * Настройка MutationObserver для динамически добавленных редакторов
     */
    setupEditorObserver() {
        if (this.observer) return;

        this.observer = new MutationObserver((mutations) => {
            for (const mutation of mutations) {
                for (const node of mutation.removedNodes) {
                    if (node.nodeType === 1) {
                        if (
                            node.id === 'note-view-editor' ||
                            node.querySelector?.('#note-view-editor')
                        ) {
                            if (this.editorInstance) {
                                this.editorInstance.destroy();
                                this.editorInstance = null;
                            }
                            this.originalContent = null;
                        }
                    }
                }

                for (const node of mutation.addedNodes) {
                    if (node.nodeType === 1) {
                        if (node.id === 'note-view-editor') {
                            setTimeout(() => this.initEditor(), 50);
                            return;
                        }
                        if (node.querySelector?.('#note-view-editor')) {
                            setTimeout(() => this.initEditor(), 50);
                            return;
                        }
                    }
                }
            }
        });

        this.observer.observe(document.body, {
            childList: true,
            subtree: true,
        });
    }

    /**
     * Настройка слушателей событий Livewire
     */
    setupLivewireListeners() {
        // Слушаем запрос на получение контента от PHP
        document.addEventListener('getEditorContent', () => {
            sendContentToLivewire('note-view-editor');
        });

        // Загрузка данных при редактировании
        document.addEventListener('noteLoaded', (e) => {
            let parsedContent = e.detail?.content || e.detail;
            if (typeof parsedContent === 'string') {
                try {
                    parsedContent = JSON.parse(parsedContent);
                } catch {
                    parsedContent = '';
                }
            }

            this.originalContent = parsedContent;
            this.originalImagePaths = Array.from(this.extractImagePathsFromContent(parsedContent));
            this.previousImagePaths = this.extractImagePathsFromContent(parsedContent);
            this.newImagePaths = [];

            const editorElement = document.querySelector('#note-view-editor');
            if (editorElement && editorElement._editor) {
                editorElement._editor.commands.setContent(parsedContent);
            }
        });

        // Восстановление оригинального состояния
        document.addEventListener('restore-note-original-state', () => {
            this.restoreOriginalState();
        });

        // Обработка событий update-safe-id и update-archive-id
        document.addEventListener('update-safe-id', (e) => {
            if (typeof Livewire !== 'undefined') {
                Livewire.dispatch('updateSafeId', {
                    id: e.detail.id,
                });
            }
        });

        document.addEventListener('update-archive-id', (e) => {
            if (typeof Livewire !== 'undefined') {
                Livewire.dispatch('updateArchiveId', {
                    id: e.detail.id,
                });
            }
        });
    }

    /**
     * Уничтожение редактора
     */
    destroy() {
        const editorElement = document.querySelector('#note-view-editor');
        if (editorElement && editorElement._editor) {
            editorElement._editor.destroy();
            editorElement._editor = null;
        }
        if (this.editorInstance) {
            this.editorInstance.destroy();
            this.editorInstance = null;
        }
        this.originalImagePaths = [];
        this.newImagePaths = [];
        this.originalContent = null;
        this.previousImagePaths = new Set();
        this.initialized = false;
    }

    /**
     * Установка оригинального контента
     */
    setOriginalContent(content, imagePaths) {
        this.originalContent = content;
        this.originalImagePaths = imagePaths || [];
        this.newImagePaths = [];
        this.previousImagePaths = this.extractImagePathsFromContent(content);

        const editorElement = document.querySelector('#note-view-editor');
        if (editorElement && editorElement._editor) {
            editorElement._editor.commands.setContent(content);
        }
    }

    /**
     * Получение оригинальных путей изображений
     */
    getOriginalImagePaths() {
        return this.originalImagePaths;
    }

    /**
     * Получение новых путей изображений
     */
    getNewImagePaths() {
        return this.newImagePaths;
    }

    /**
     * Восстановление оригинального состояния
     */
    restoreOriginalState() {
        const editorElement = document.querySelector('#note-view-editor');
        if (!editorElement || !editorElement._editor || !this.originalContent) {
            return Promise.resolve();
        }

        editorElement._editor.commands.setContent(this.originalContent);
        this.previousImagePaths = this.extractImagePathsFromContent(this.originalContent);
        this.newImagePaths = [];

        return Promise.resolve();
    }

    /**
     * Показ индикатора автосохранения
     */
    showAutosaveIndicator() {
        const indicator = document.getElementById('autosave-indicator');
        if (!indicator) return;

        indicator.classList.remove('hidden');
        indicator.textContent = 'Автосохранено';
        indicator.classList.add('text-green-600');

        setTimeout(() => {
            indicator.classList.add('hidden');
        }, 3000);
    }
}
