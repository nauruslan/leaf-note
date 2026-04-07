import { destroyNoteEditor, initNoteEditor } from './note-editor';

// Приватное состояние модуля (замыкание)
let editorInstance = null;
let uploadedImages = [];
let lastContent = null;

export function initCreateNoteEditor() {
    uploadedImages = [];

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
            // Обновляем скрытый input для синхронизации с Livewire
            const contentInput = document.getElementById('note-content-input');
            if (contentInput) {
                contentInput.value = JSON.stringify(json);
                // Триггерим событие input для Livewire
                contentInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
        },
    });

    editorInstance = editor;

    // Сохраняем начальные данные
    lastContent = editor.getJSON();

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

export function deleteAllUploadedImages() {
    if (uploadedImages.length === 0) {
        return Promise.resolve();
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    const deletePromises = uploadedImages.map((path) => {
        return fetch('/notes/delete-image', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                Accept: 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ path }),
        })
            .then((response) => {
                if (!response.ok) {
                    console.error(
                        `[CreateNoteEditor] Ошибка удаления изображения ${path}:`,
                        response.statusText,
                    );
                }
                return response.json();
            })
            .catch((error) => {
                console.error(`[CreateNoteEditor] Ошибка удаления изображения ${path}:`, error);
            });
    });

    return Promise.all(deletePromises).then(() => {
        uploadedImages = [];
    });
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
