import {
    destroyNoteEditor,
    getEditorContent,
    initNoteEditor,
    sendContentToLivewire,
} from './note-editor';

let uploadedImages = [];

export function initCreateNoteEditor() {
    uploadedImages = [];

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
    });

    return editor;
}

export function destroyCreateNoteEditor() {
    destroyNoteEditor('create-note-editor');
    uploadedImages = [];
}

export function getCreateNoteEditorContent() {
    return getEditorContent('create-note-editor');
}

export function sendCreateNoteContentToLivewire() {
    sendContentToLivewire('create-note-editor');
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
    const editorElement = document.querySelector('#create-note-editor');
    if (editorElement && !editorElement._editor) {
        initCreateNoteEditor();
    }
}

const createNoteObserver = new MutationObserver((mutations) => {
    for (const mutation of mutations) {
        for (const node of mutation.addedNodes) {
            if (node.nodeType === 1) {
                if (
                    node.id === 'create-note-editor' ||
                    node.querySelector?.('#create-note-editor')
                ) {
                    setTimeout(autoInitCreateNoteEditor, 50);
                    return;
                }
            }
        }
    }
});

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
