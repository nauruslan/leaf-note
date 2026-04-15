import { restoreImage, softDeleteImage } from './editor-helpers';
import { initNoteEditor, sendContentToLivewire } from './note-editor';

let originalImagePaths = [];
let newImagePaths = [];
let originalContent = null;
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

    // Изображения, которые были удалены из контента
    for (const path of previousPaths) {
        if (!currentPaths.has(path)) {
            // Изображение удалено из контента — помечаем на удаление
            softDeleteImage(path);
        }
    }

    // Изображения, которые появились в контенте
    for (const path of currentPaths) {
        if (!previousPaths.has(path)) {
            // Изображение появилось в контенте — восстанавливаем из списка на удаление
            restoreImage(path);
        }
    }

    // Обновляем предыдущее состояние
    previousImagePaths = currentPaths;
}

export function initNoteViewEditor(content = '') {
    originalImagePaths = [];
    newImagePaths = [];
    previousImagePaths = new Set();
    originalContent = content;

    const editor = initNoteEditor({
        elementId: 'note-view-editor',
        content: content,
        placeholder: 'Начните вводить текст заметки...',
        type: 'note-view',
        onImageUploaded: (imagePath) => {
            if (imagePath && !newImagePaths.includes(imagePath)) {
                newImagePaths.push(imagePath);
            }
        },
        onUpdate: (editor) => {
            // Сохраняем последние данные в замыкании
            const json = editor.getJSON();

            // Синхронизируем состояние изображений (мягкое удаление/восстановление)
            syncImageState(json);

            // Обновляем скрытый input для синхронизации с Livewire
            const contentInput = document.getElementById('note-view-content-input');
            if (contentInput) {
                contentInput.value = JSON.stringify(json);
                // Триггерим событие input для Livewire с debounce
                clearTimeout(window.noteViewUpdateTimeout);
                window.noteViewUpdateTimeout = setTimeout(() => {
                    contentInput.dispatchEvent(new Event('input', { bubbles: true }));
                    // Показываем индикатор автосохранения
                    showAutosaveIndicator();
                }, 300);
            }
        },
    });

    // Инициализируем предыдущее состояние изображений
    previousImagePaths = extractImagePathsFromContent(content);

    return editor;
}

export function setOriginalContent(content, imagePaths) {
    originalContent = content;
    originalImagePaths = imagePaths || [];
    newImagePaths = [];
    previousImagePaths = extractImagePathsFromContent(content);

    const editorElement = document.querySelector('#note-view-editor');
    if (editorElement && editorElement._editor) {
        editorElement._editor.commands.setContent(content);
    }
}

export function getOriginalImagePaths() {
    return originalImagePaths;
}

export function getNewImagePaths() {
    return newImagePaths;
}

export function restoreOriginalState() {
    const editorElement = document.querySelector('#note-view-editor');
    if (!editorElement || !editorElement._editor || !originalContent) {
        return Promise.resolve();
    }

    // Восстанавливаем оригинальный контент
    editorElement._editor.commands.setContent(originalContent);
    previousImagePaths = extractImagePathsFromContent(originalContent);
    newImagePaths = [];

    return Promise.resolve();
}

function autoInitNoteViewEditor() {
    const editorElement = document.querySelector('#note-view-editor');
    if (editorElement && !editorElement._editor) {
        let content = editorElement.dataset.content;
        if (content) {
            try {
                content = JSON.parse(content);
                originalContent = content;
                originalImagePaths = Array.from(extractImagePathsFromContent(content));
                previousImagePaths = extractImagePathsFromContent(content);
            } catch (e) {
                content = '';
            }
        } else {
            content = '';
        }
        initNoteViewEditor(content);
    }
}

const noteViewObserver = new MutationObserver((mutations) => {
    for (const mutation of mutations) {
        for (const node of mutation.addedNodes) {
            if (node.nodeType === 1) {
                if (node.id === 'note-view-editor' || node.querySelector?.('#note-view-editor')) {
                    setTimeout(autoInitNoteViewEditor, 50);
                    return;
                }
            }
        }
    }
});

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        noteViewObserver.observe(document.body, { childList: true, subtree: true });
        autoInitNoteViewEditor();
    });
} else {
    noteViewObserver.observe(document.body, { childList: true, subtree: true });
    autoInitNoteViewEditor();
}

Livewire.on('getEditorContent', () => {
    sendContentToLivewire('note-view-editor');
});

Livewire.on('noteLoaded', (data) => {
    let parsedContent = data?.content || data;
    if (typeof parsedContent === 'string') {
        try {
            parsedContent = JSON.parse(parsedContent);
        } catch (e) {
            parsedContent = '';
        }
    }

    originalContent = parsedContent;
    originalImagePaths = Array.from(extractImagePathsFromContent(parsedContent));
    previousImagePaths = extractImagePathsFromContent(parsedContent);
    newImagePaths = [];

    const editorElement = document.querySelector('#note-view-editor');
    if (editorElement && editorElement._editor) {
        editorElement._editor.commands.setContent(parsedContent);
    }
});

document.addEventListener('restore-note-original-state', () => {
    restoreOriginalState();
});

function showAutosaveIndicator() {
    const indicator = document.getElementById('autosave-indicator');
    if (!indicator) return;

    indicator.classList.remove('hidden');
    indicator.textContent = 'Автосохранено';
    indicator.classList.add('text-green-600');

    setTimeout(() => {
        indicator.classList.add('hidden');
    }, 3000);
}
