import { destroyNoteEditor, initNoteEditor } from './note-editor';
import { executePendingDeletion, restoreImage, softDeleteImage } from './editor-helpers';

// Приватное состояние модуля (замыкание)
let editorInstance = null;
let uploadedImages = [];
let lastContent = null;
let previousImagePaths = new Set(); // Пути изображений из предыдущего состояния

/**
 * Извлечь пути изображений из контента редактора
 */
function extractImagePathsFromContent(content) {
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
function syncImageState(currentContent) {
    const currentPaths = extractImagePathsFromContent(currentContent);
    const previousPaths = previousImagePaths;

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
    previousImagePaths = currentPaths;
}

export function initCreateNoteEditor() {
    uploadedImages = [];
    previousImagePaths = new Set();

    // Уничтожаем существующий редактор
    if (editorInstance) {
        editorInstance.destroy();
        editorInstance = null;
    }

    lastContent = null;

    const editor = initNoteEditor({
        elementId: 'create-note-editor',
        content: '',
        placeholder: 'Начните вводить текст заметки...',
        type: 'create-note',
        onImageUploaded: (imagePath) => {
            if (imagePath && !uploadedImages.includes(imagePath)) {
                uploadedImages.push(imagePath);
            }
        },
        onUpdate: (editor) => {
            // Сохраняем последние данные в замыкании
            const json = editor.getJSON();
            lastContent = json;

            // Синхронизируем состояние изображений (мягкое удаление/восстановление)
            syncImageState(json);

            // Обновляем скрытый input для синхронизации с Livewire
            const contentInput = document.getElementById('note-content-input');
            if (contentInput) {
                contentInput.value = JSON.stringify(json);
                // Триггерим событие input для Livewire с debounce
                clearTimeout(window.createNoteUpdateTimeout);
                window.createNoteUpdateTimeout = setTimeout(() => {
                    contentInput.dispatchEvent(new Event('input', { bubbles: true }));
                }, 300);
            }
        },
    });

    editorInstance = editor;

    // Сохраняем начальные данные
    lastContent = editor.getJSON();
    previousImagePaths = extractImagePathsFromContent(lastContent);

    return editor;
}

export function destroyCreateNoteEditor() {
    destroyNoteEditor('create-note-editor');
    if (editorInstance) {
        editorInstance.destroy();
        editorInstance = null;
    }
    uploadedImages = [];
    lastContent = null;
    previousImagePaths = new Set();
}

export function getCreateNoteEditorContent() {
    return lastContent || (editorInstance ? editorInstance.getJSON() : null);
}

export function sendCreateNoteContentToLivewire() {
    const content = getCreateNoteEditorContent();
    if (content) {
        Livewire.dispatch('noteContentReady', { content: JSON.stringify(content) });
    }
}

/**
 * Выполнить фактическое удаление всех помеченных изображений
 * Вызывается при сохранении заметки или уходе со страницы
 */
export function deleteAllUploadedImages() {
    return executePendingDeletion();
}

function autoInitCreateNoteEditor() {
    const container = document.getElementById('create-note-editor');
    if (container) {
        if (!editorInstance) {
            initCreateNoteEditor();
        }
    }
}

// MutationObserver для отслеживания появления и удаления элементов редактора
const createNoteObserver = new MutationObserver((mutations) => {
    for (const mutation of mutations) {
        for (const node of mutation.removedNodes) {
            if (node.nodeType === 1) {
                if (
                    node.id === 'create-note-editor' ||
                    node.querySelector?.('#create-note-editor')
                ) {
                    if (editorInstance) {
                        editorInstance.destroy();
                        editorInstance = null;
                    }
                    lastContent = null;
                }
            }
        }

        for (const node of mutation.addedNodes) {
            if (node.nodeType === 1) {
                if (node.id === 'create-note-editor') {
                    setTimeout(autoInitCreateNoteEditor, 50);
                    return;
                }
                if (node.querySelector?.('#create-note-editor')) {
                    setTimeout(autoInitCreateNoteEditor, 50);
                    return;
                }
            }
        }
    }
});

// Инициализация при загрузке страницы
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        createNoteObserver.observe(document.body, { childList: true, subtree: true });
        autoInitCreateNoteEditor();
    });
} else {
    createNoteObserver.observe(document.body, { childList: true, subtree: true });
    autoInitCreateNoteEditor();
}

document.addEventListener('delete-uploaded-images', () => {
    deleteAllUploadedImages();
});

Livewire.on('getEditorContent', () => {
    sendCreateNoteContentToLivewire();
});
