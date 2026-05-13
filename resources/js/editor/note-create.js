import { restoreImage, softDeleteImage } from './editor-helpers';
import { initNoteEditor } from './note';

/**
 * Класс для инициализации и управления редактором заметок при создании
 */
export default class CreateNoteEditor {
    constructor() {
        this.initialized = false;
        this.observer = null;
        this.editorInstance = null;
        this.uploadedImages = [];
        this.lastContent = null;
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

        // Изображения, которые были удалены из контента (undo добавления или удаление)
        for (const path of previousPaths) {
            if (!currentPaths.has(path)) {
                // Изображение удалено из контента — помечаем на удаление
                softDeleteImage(path);
            }
        }

        // Изображения, которые появились в контенте (redo удаления или добавление)
        for (const path of currentPaths) {
            if (!previousPaths.has(path)) {
                // Изображение появилось в контенте — восстанавливаем из списка на удаление
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
        const editorElement = document.querySelector('#create-note-editor');
        if (!editorElement) return;

        // Уничтожаем существующий редактор
        if (editorElement._editor) {
            editorElement._editor.destroy();
            editorElement._editor = null;
        }

        this.uploadedImages = [];
        this.previousImagePaths = new Set();
        this.lastContent = null;

        this.createEditor();
    }

    /**
     * Создание экземпляра редактора
     */
    createEditor() {
        // Уничтожаем существующий редактор
        if (this.editorInstance) {
            this.editorInstance.destroy();
            this.editorInstance = null;
        }

        const editor = initNoteEditor({
            elementId: 'create-note-editor',
            content: '',
            placeholder: 'Начните вводить текст заметки...',
            type: 'create-note',
            onImageUploaded: (imagePath) => {
                if (imagePath && !this.uploadedImages.includes(imagePath)) {
                    this.uploadedImages.push(imagePath);
                }
            },
            onUpdate: (editor) => {
                // Сохраняем последние данные
                const json = editor.getJSON();

                this.lastContent = json;

                // Синхронизируем состояние изображений (мягкое удаление/восстановление)
                this.syncImageState(json);

                // Обновляем скрытый input для синхронизации с Livewire
                const contentInput = document.getElementById('note-content-input');
                if (contentInput) {
                    // Триггерим событие input для Livewire с debounce
                    clearTimeout(window.createNoteUpdateTimeout);
                    const valueToSend = JSON.stringify(json);
                    window.createNoteUpdateTimeout = setTimeout(() => {
                        const editorElement = document.getElementById('create-note-editor');
                        if (editorElement && this.editorInstance) {
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

        // Сохраняем начальные данные
        this.lastContent = editor.getJSON();
        this.previousImagePaths = this.extractImagePathsFromContent(this.lastContent);

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
                            node.id === 'create-note-editor' ||
                            node.querySelector?.('#create-note-editor')
                        ) {
                            if (this.editorInstance) {
                                this.editorInstance.destroy();
                                this.editorInstance = null;
                            }
                            this.lastContent = null;
                        }
                    }
                }

                for (const node of mutation.addedNodes) {
                    if (node.nodeType === 1) {
                        if (node.id === 'create-note-editor') {
                            setTimeout(() => this.initEditor(), 50);
                            return;
                        }
                        if (node.querySelector?.('#create-note-editor')) {
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
            this.sendContentToLivewire();
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
        const editorElement = document.querySelector('#create-note-editor');
        if (editorElement && editorElement._editor) {
            editorElement._editor.destroy();
            editorElement._editor = null;
        }
        if (this.editorInstance) {
            this.editorInstance.destroy();
            this.editorInstance = null;
        }
        this.uploadedImages = [];
        this.lastContent = null;
        this.previousImagePaths = new Set();
        this.initialized = false;
    }

    /**
     * Получение контента редактора
     */
    getContent() {
        return this.lastContent || (this.editorInstance ? this.editorInstance.getJSON() : null);
    }

    /**
     * Отправка контента в Livewire
     */
    sendContentToLivewire() {
        const content = this.getContent();
        if (content) {
            Livewire.dispatch('noteContentReady', { content: JSON.stringify(content) });
        }
    }

    /**
     * Показ индикатора автосохранения
     */
    showAutosaveIndicator() {
        const indicator = document.getElementById('autosave-indicator');
        if (!indicator) return;

        indicator.classList.remove('hidden');
        indicator.textContent = 'Сохранено';
        indicator.classList.add('text-green-600');

        setTimeout(() => {
            indicator.classList.add('hidden');
        }, 3000);
    }
}
