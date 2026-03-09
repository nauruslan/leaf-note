import { initNoteEditor, sendContentToLivewire } from './note-editor';

export function initNoteViewEditor(content = '') {
    return initNoteEditor({
        elementId: 'note-view-editor',
        content: content,
        placeholder: 'Начните вводить текст заметки...',
        type: 'note-view',
    });
}

function autoInitNoteViewEditor() {
    const editorElement = document.querySelector('#note-view-editor');
    if (editorElement && !editorElement._editor) {
        let content = editorElement.dataset.content;
        if (content) {
            try {
                content = JSON.parse(content);
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
    const editorElement = document.querySelector('#note-view-editor');
    if (editorElement && editorElement._editor) {
        editorElement._editor.commands.setContent(parsedContent);
    }
});
