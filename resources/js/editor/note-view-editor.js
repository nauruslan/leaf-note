import { initNoteEditor, sendContentToLivewire } from './note-editor';

let originalImagePaths = [];
let newImagePaths = [];
let originalContent = null;

export function initNoteViewEditor(content = '') {
    originalImagePaths = [];
    newImagePaths = [];
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

    return editor;
}

export function setOriginalContent(content, imagePaths) {
    originalContent = content;
    originalImagePaths = imagePaths || [];
    newImagePaths = [];

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

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    const deletePromises = newImagePaths.map((path) => {
        return fetch('/notes/delete-image', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                Accept: 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ path }),
        }).catch((error) => {
            console.error('[NoteViewEditor] Ошибка удаления нового изображения:', path, error);
        });
    });

    return Promise.all(deletePromises).then(() => {
        editorElement._editor.commands.setContent(originalContent);
        newImagePaths = [];
    });
}

export function extractImagePathsFromContent(content) {
    const paths = [];
    if (!content || !content.content) return paths;

    function traverse(node) {
        if (!node) return;
        if (node.type === 'image' && node.attrs?.path) {
            paths.push(node.attrs.path);
        }
        if (node.content) {
            node.content.forEach(traverse);
        }
    }

    traverse(content);
    return paths;
}

function autoInitNoteViewEditor() {
    const editorElement = document.querySelector('#note-view-editor');
    if (editorElement && !editorElement._editor) {
        let content = editorElement.dataset.content;
        if (content) {
            try {
                content = JSON.parse(content);
                originalContent = content;
                originalImagePaths = extractImagePathsFromContent(content);
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
    originalImagePaths = extractImagePathsFromContent(parsedContent);
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
