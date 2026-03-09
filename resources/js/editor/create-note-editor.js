import {
    destroyNoteEditor,
    getEditorContent,
    initNoteEditor,
    sendContentToLivewire,
} from './note-editor';

export function initCreateNoteEditor() {
    return initNoteEditor({
        elementId: 'create-note-editor',
        content: '',
        placeholder: 'Начните вводить текст заметки...',
        type: 'create-note',
    });
}

export function destroyCreateNoteEditor() {
    destroyNoteEditor('create-note-editor');
}

export function getCreateNoteEditorContent() {
    return getEditorContent('create-note-editor');
}

export function sendCreateNoteContentToLivewire() {
    sendContentToLivewire('create-note-editor');
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

Livewire.on('getEditorContent', () => {
    sendCreateNoteContentToLivewire();
});
