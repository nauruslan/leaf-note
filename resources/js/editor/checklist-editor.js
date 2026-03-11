import { Editor } from '@tiptap/core';
import { Color } from '@tiptap/extension-color';
import Highlight from '@tiptap/extension-highlight';
import Link from '@tiptap/extension-link';
import Paragraph from '@tiptap/extension-paragraph';
import Placeholder from '@tiptap/extension-placeholder';
import { TextStyle } from '@tiptap/extension-text-style';
import StarterKit from '@tiptap/starter-kit';

import { Checklist, ChecklistItem, ChecklistNavigation } from './checklist-extension';
import {
    closeLinkModal,
    COLOR_PALETTE,
    createPalette,
    getContrastColor,
    hideAllPalettes,
    initLinkModal,
    openLinkModal,
    updateToolbarButtons,
} from './editor-helpers';

const DEFAULT_CONFIG = {
    extensions: [
        StarterKit.configure({
            bulletList: false,
            orderedList: false,
            taskList: false,
            codeBlock: false,
            blockquote: false,
            horizontalRule: false,
            hardBreak: false,
            link: false,
            paragraph: false,
            gapcursor: false,
        }),
        Paragraph.extend({
            group: 'block',
            content: 'inline*',
        }),
        TextStyle,
        Color.configure({ types: ['textStyle'] }),
        Placeholder.configure({
            placeholder: 'Нажмите "Добавить задачу", чтобы создать список...',
        }),
        Link.configure({
            openOnClick: false,
            HTMLAttributes: { class: 'text-indigo-600 hover:text-indigo-800 underline' },
        }),
        Highlight.configure({ multicolor: true }),
        ChecklistNavigation,
        Checklist,
        ChecklistItem,
    ],
    editorProps: {
        attributes: {
            class: 'prose prose-indigo max-w-none focus:outline-none min-h-[400px] checklist-editor',
            spellcheck: 'false',
        },
    },
    onContentError: ({ error }) => {
        console.warn('[ChecklistEditor] Content error:', error);
    },
};

export function initChecklistEditor(options) {
    const {
        elementId,
        content = '',
        placeholder = 'Нажмите "Добавить задачу", чтобы создать список...',
        onUpdate,
        onSelectionUpdate,
    } = options;

    const editorElement = document.querySelector(`#${elementId}`);

    if (!editorElement) {
        console.error(`[ChecklistEditor] Element #${elementId} not found`);
        return null;
    }

    if (editorElement._editor) {
        editorElement._editor.destroy();
        editorElement._editor = null;
    }

    editorElement.innerHTML = '';
    initLinkModal();

    const config = { ...DEFAULT_CONFIG, element: editorElement, content: content };

    config.extensions = config.extensions.map((ext) => {
        if (ext.name === 'placeholder') {
            return Placeholder.configure({ placeholder });
        }
        return ext;
    });

    config.onUpdate = ({ editor }) => {
        if (onUpdate) onUpdate(editor);
    };

    config.onSelectionUpdate = ({ editor }) => {
        updateToolbarButtons(editor);
        if (onSelectionUpdate) onSelectionUpdate(editor);
    };

    const editor = new Editor(config);
    editorElement._editor = editor;

    // Если документ пуст, вставляем чеклист с одним пунктом
    if (!content || (typeof content === 'string' && content.trim() === '')) {
        editor.commands.insertChecklist();
        // Устанавливаем фокус и курсор в первый пункт через TipTap
        setTimeout(() => {
            const { state } = editor;
            let firstItemPos = null;
            state.doc.descendants((node, pos) => {
                if (node.type.name === 'checklistItem') {
                    firstItemPos = pos + 1; // Позиция внутри paragraph
                    return false;
                }
            });
            if (firstItemPos !== null) {
                // Используем TipTap focus() для правильной установки фокуса
                editor.commands.focus(firstItemPos, { scrollIntoView: false });
                editor.commands.setTextSelection(firstItemPos);
            }
        }, 100);
    }

    const preventRootFocus = () => {
        const proseMirror = editorElement.querySelector('.ProseMirror');
        if (!proseMirror) return;

        const handleMouseDown = (e) => {
            let target = e.target;
            if (target.nodeType === 3) {
                target = target.parentElement;
            }
            // Разрешаем клик по кнопке удаления и чекбоксу
            if (target.closest('.checklist-delete-btn, [data-action="delete-item"]')) {
                return; // Пропускаем клик по кнопке удаления
            }
            if (target.closest('.checklist-checkbox, [data-action="toggle-check"]')) {
                return; // Пропускаем клик по чекбоксу
            }
            const isInsideChecklistItem = target.closest(
                '.checklist-item, [data-type="checklist-item"], .checklist-item-content',
            );
            if (!isInsideChecklistItem) {
                e.preventDefault();
                e.stopPropagation();
                const firstItem = editorElement.querySelector('.checklist-item-content');
                if (firstItem) {
                    firstItem.focus();
                } else {
                    const addBtn = document.getElementById('add-checklist-task-btn');
                    if (addBtn) {
                        addBtn.click();
                    }
                }
            }
        };

        proseMirror.addEventListener('mousedown', handleMouseDown);

        editor.on('destroy', () => {
            proseMirror.removeEventListener('mousedown', handleMouseDown);
        });
    };

    setTimeout(() => {
        initToolbarButtons(editor);
        updateToolbarButtons(editor);
        preventRootFocus();
    }, 50);

    return editor;
}

export function destroyChecklistEditor(elementId) {
    const editorElement = document.querySelector(`#${elementId}`);
    if (!editorElement) return;

    if (editorElement._editor) {
        editorElement._editor.destroy();
        editorElement._editor = null;
    }

    editorElement.innerHTML = '';
    editorElement.classList.remove('tiptap');

    const modal = document.getElementById('link-modal');
    if (modal) modal.remove();
}

export function getChecklistEditorContent(elementId) {
    const editorElement = document.querySelector(`#${elementId}`);
    if (!editorElement || !editorElement._editor) return null;
    return editorElement._editor.getJSON();
}

export function sendChecklistContentToLivewire(elementId) {
    const content = getChecklistEditorContent(elementId);
    if (content !== null) {
        Livewire.dispatch('editorContent', { content });
    }
}

function initToolbarButtons(editor) {
    const buttonActions = {
        bold: () => editor.chain().focus().toggleBold().run(),
        italic: () => editor.chain().focus().toggleItalic().run(),
        undo: () => editor.chain().focus().undo().run(),
        redo: () => editor.chain().focus().redo().run(),
    };

    Object.entries(buttonActions).forEach(([action, handler]) => {
        const btn = document.querySelector(`[data-editor-action="${action}"]`);
        if (btn) {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                handler();
                updateToolbarButtons(editor);
            });
        }
    });

    const linkBtn = document.querySelector('[data-editor-action="link"]');
    if (linkBtn) {
        linkBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            openLinkModal(editor);
        });
    }

    const colorBtn = document.querySelector('[data-editor-action="color"]');
    if (colorBtn) {
        colorBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            createPalette(editor, colorBtn, 'color', COLOR_PALETTE);
        });
    }

    const highlightBtn = document.querySelector('[data-editor-action="highlight"]');
    if (highlightBtn) {
        highlightBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            createPalette(editor, highlightBtn, 'highlight', COLOR_PALETTE);
        });
    }
}

function autoInitChecklistEditor() {
    const editorElement = document.querySelector('#checklist-editor');
    if (editorElement && !editorElement._editor) {
        initChecklistEditor({
            elementId: 'checklist-editor',
            content: '',
            placeholder: 'Нажмите "Добавить задачу", чтобы создать список...',
        });
    }

    const addTaskBtn = document.getElementById('add-checklist-task-btn');
    if (addTaskBtn && !addTaskBtn._checklistHandlerAttached) {
        addTaskBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();

            const editorElement = document.querySelector('#checklist-editor');
            if (!editorElement || !editorElement._editor) {
                console.error('[ChecklistEditor] Editor not initialized');
                return;
            }

            const editor = editorElement._editor;
            const { state } = editor;

            const hasChecklist = state.doc.firstChild?.type.name === 'checklist';

            if (!hasChecklist) {
                // Создаём новый чеклист и устанавливаем курсор в первый пункт
                editor.commands.insertChecklist();
                setTimeout(() => {
                    editor.commands.focus();
                    const { state } = editor;
                    let firstItemPos = null;
                    state.doc.descendants((node, pos) => {
                        if (node.type.name === 'checklistItem') {
                            firstItemPos = pos + 1;
                            return false;
                        }
                    });
                    if (firstItemPos !== null) {
                        editor.commands.setTextSelection(firstItemPos);
                    }
                }, 50);
            } else {
                // Добавляем новый пункт в существующий чеклист
                editor.commands.appendChecklistItem();
                // Дополнительно фокусируем редактор и устанавливаем выделение в конец
                setTimeout(() => {
                    editor.commands.focus();
                    const { state } = editor;
                    let lastItemPos = null;
                    // Находим последний checklistItem
                    state.doc.descendants((node, pos) => {
                        if (node.type.name === 'checklistItem') {
                            lastItemPos = pos + node.nodeSize - 2; // перед закрывающим тегом, внутри параграфа
                        }
                    });
                    if (lastItemPos !== null) {
                        editor.commands.setTextSelection(lastItemPos);
                    }
                }, 50);
            }
        });

        addTaskBtn._checklistHandlerAttached = true;
    }
}

const checklistObserver = new MutationObserver((mutations) => {
    for (const mutation of mutations) {
        for (const node of mutation.addedNodes) {
            if (node.nodeType === 1) {
                if (node.id === 'checklist-editor' || node.querySelector?.('#checklist-editor')) {
                    setTimeout(autoInitChecklistEditor, 50);
                    return;
                }
            }
        }
    }
});

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        checklistObserver.observe(document.body, { childList: true, subtree: true });
        autoInitChecklistEditor();
    });
} else {
    checklistObserver.observe(document.body, { childList: true, subtree: true });
    autoInitChecklistEditor();
}

document.addEventListener('delete-uploaded-images', () => {
    deleteAllUploadedImages();
});

Livewire.on('getEditorContent', () => {
    sendChecklistContentToLivewire('checklist-editor');
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

    const editorElement = document.querySelector('#checklist-editor');
    if (editorElement && editorElement._editor) {
        editorElement._editor.commands.setContent(parsedContent);
    }
});

export {
    closeLinkModal,
    COLOR_PALETTE,
    createPalette,
    getContrastColor,
    hideAllPalettes,
    initLinkModal,
    openLinkModal,
    updateToolbarButtons,
};
