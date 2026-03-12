import {
    initChecklistEditor,
    sendChecklistContentToLivewire,
    getChecklistEditorContent,
} from './checklist-editor';

export function initCreateChecklistEditor() {
    const editorElement = document.querySelector('#create-checklist-editor');
    if (!editorElement) {
        console.error('[CreateChecklistEditor] Element #create-checklist-editor not found');
        return null;
    }

    // Уничтожаем существующий редактор перед повторной инициализацией
    if (editorElement._editor) {
        editorElement._editor.destroy();
        editorElement._editor = null;
    }

    const editor = initChecklistEditor({
        elementId: 'create-checklist-editor',
        content: '',
        placeholder: 'Нажмите "Добавить задачу", чтобы создать список...',
    });

    return editor;
}

export function destroyCreateChecklistEditor() {
    const editorElement = document.querySelector('#create-checklist-editor');
    if (editorElement && editorElement._editor) {
        editorElement._editor.destroy();
        editorElement._editor = null;
    }
}

export function getCreateChecklistEditorContent() {
    return getChecklistEditorContent('create-checklist-editor');
}

export function sendCreateChecklistContentToLivewire() {
    sendChecklistContentToLivewire('create-checklist-editor');
}

function autoInitCreateChecklistEditor() {
    const editorElement = document.querySelector('#create-checklist-editor');
    if (editorElement && !editorElement._editor) {
        initCreateChecklistEditor();
    }
}

const createChecklistObserver = new MutationObserver((mutations) => {
    for (const mutation of mutations) {
        for (const node of mutation.addedNodes) {
            if (node.nodeType === 1) {
                if (
                    node.id === 'create-checklist-editor' ||
                    node.querySelector?.('#create-checklist-editor')
                ) {
                    setTimeout(autoInitCreateChecklistEditor, 50);
                    return;
                }
            }
        }
    }
});

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        createChecklistObserver.observe(document.body, { childList: true, subtree: true });
        autoInitCreateChecklistEditor();
    });
} else {
    createChecklistObserver.observe(document.body, { childList: true, subtree: true });
    autoInitCreateChecklistEditor();
}

Livewire.on('getEditorContent', () => {
    sendCreateChecklistContentToLivewire();
});

Livewire.on('saveChecklist', (data) => {
    const folderId = data?.folderId || null;
    const color = data?.color || 'default';
    Livewire.dispatch('triggerSave', { folderId, color });
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

    const editorElement = document.querySelector('#create-checklist-editor');
    if (editorElement && editorElement._editor) {
        editorElement._editor.commands.setContent(parsedContent);
    } else if (editorElement) {
        // Если редактор ещё не инициализирован, сохраняем контент для последующей инициализации
        initCreateChecklistEditor();
        setTimeout(() => {
            if (editorElement._editor) {
                editorElement._editor.commands.setContent(parsedContent);
            }
        }, 100);
    }
});
