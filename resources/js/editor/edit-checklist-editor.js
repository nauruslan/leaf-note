import { initChecklistEditor, sendChecklistContentToLivewire } from './checklist-editor';
import { initChecklistProgressBar } from './checklist-progress';

let originalContent = null;
let pendingContent = null;
let progressBar = null;

export function initEditChecklistEditor(content = '') {
    originalContent = content;

    const editorElement = document.querySelector('#edit-checklist-editor');
    if (!editorElement) {
        console.error('[EditChecklistEditor] Element #edit-checklist-editor not found');
        return null;
    }

    if (editorElement._editor) {
        editorElement._editor.destroy();
        editorElement._editor = null;
    }

    const editor = initChecklistEditor({
        elementId: 'edit-checklist-editor',
        content: content,
        placeholder: 'Нажмите "Добавить задачу", чтобы создать список...',
    });

    if (editor) {
        progressBar = initChecklistProgressBar(editor, 'checklist-progress-bar');
    }

    return editor;
}

export function setOriginalContent(content) {
    originalContent = content;

    const editorElement = document.querySelector('#edit-checklist-editor');
    if (editorElement && editorElement._editor) {
        editorElement._editor.commands.setContent(content);
    }
}

export function resetEditChecklistEditor() {
    originalContent = null;
    pendingContent = null;

    const editorElement = document.querySelector('#edit-checklist-editor');
    if (editorElement && editorElement._editor) {
        editorElement._editor.destroy();
        editorElement._editor = null;
    }
}

function autoInitEditChecklistEditor() {
    const editorElement = document.querySelector('#edit-checklist-editor');
    if (editorElement && !editorElement._editor) {
        const content = pendingContent || '';
        initEditChecklistEditor(content);
    }
}

const editChecklistObserver = new MutationObserver((mutations) => {
    for (const mutation of mutations) {
        for (const node of mutation.addedNodes) {
            if (node.nodeType === 1) {
                if (
                    node.id === 'edit-checklist-editor' ||
                    node.querySelector?.('#edit-checklist-editor')
                ) {
                    setTimeout(autoInitEditChecklistEditor, 50);
                    return;
                }
            }
        }
    }
});

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        editChecklistObserver.observe(document.body, { childList: true, subtree: true });
        autoInitEditChecklistEditor();
    });
} else {
    editChecklistObserver.observe(document.body, { childList: true, subtree: true });
    autoInitEditChecklistEditor();
}

Livewire.on('getEditorContent', () => {
    sendChecklistContentToLivewire('edit-checklist-editor');
});

Livewire.on('checklistLoaded', (data) => {
    let parsedContent = data?.content || data;
    if (typeof parsedContent === 'string') {
        try {
            parsedContent = JSON.parse(parsedContent);
        } catch (e) {
            parsedContent = '';
        }
    }

    originalContent = parsedContent;
    pendingContent = parsedContent;

    const editorElement = document.querySelector('#edit-checklist-editor');
    if (editorElement && editorElement._editor) {
        editorElement._editor.commands.setContent(parsedContent);
    } else if (editorElement) {
        initEditChecklistEditor(parsedContent);
    }
});
