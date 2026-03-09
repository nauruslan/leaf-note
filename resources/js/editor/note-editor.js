import { Editor } from '@tiptap/core';
import { Color } from '@tiptap/extension-color';
import Highlight from '@tiptap/extension-highlight';
import Placeholder from '@tiptap/extension-placeholder';
import { Table } from '@tiptap/extension-table';
import TableCell from '@tiptap/extension-table-cell';
import TableHeader from '@tiptap/extension-table-header';
import TableRow from '@tiptap/extension-table-row';
import TaskItem from '@tiptap/extension-task-item';
import TaskList from '@tiptap/extension-task-list';
import TextAlign from '@tiptap/extension-text-align';
import { TextStyle } from '@tiptap/extension-text-style';
import StarterKit from '@tiptap/starter-kit';

import {
    closeImageModal,
    closeLinkModal,
    COLOR_PALETTE,
    createPalette,
    CustomImage,
    getContrastColor,
    hideAllImageOverlays,
    hideAllPalettes,
    initImageModal,
    initLinkModal,
    openLinkModal,
    updateTableControls,
    updateToolbarButtons,
} from './editor-helpers';

const DEFAULT_CONFIG = {
    extensions: [
        StarterKit.configure({
            bulletList: { keepMarks: true, keepAttributes: false },
            orderedList: { keepMarks: true, keepAttributes: false },
            taskList: { HTMLAttributes: { class: 'not-prose' } },
            codeBlock: {
                HTMLAttributes: {
                    class: 'bg-gray-900 text-gray-100 rounded-lg p-4 my-4 overflow-x-auto font-mono text-sm',
                },
            },
        }),
        TextStyle,
        Color.configure({ types: ['textStyle'] }),
        Placeholder.configure({ placeholder: 'Начните вводить текст заметки...' }),
        CustomImage,
        TaskList,
        TaskItem.configure({ nested: true }),
        Table.configure({
            resizable: false,
            lastColumnResizable: false,
            HTMLAttributes: {
                class: 'tiptap-table w-full border-collapse my-4',
            },
        }),
        TableRow,
        TableHeader.configure({
            HTMLAttributes: {
                class: 'bg-gray-100 font-bold border border-gray-300 px-3 py-2 text-left',
            },
        }),
        TableCell.configure({
            HTMLAttributes: {
                class: 'border border-gray-300 px-3 py-2 text-left',
            },
        }),
        TextAlign.configure({
            types: ['heading', 'paragraph'],
            alignments: ['left', 'center', 'right'],
            defaultAlignment: 'left',
        }),
        Highlight.configure({
            multicolor: true,
        }),
    ],
    editorProps: {
        attributes: {
            class: 'prose prose-indigo max-w-none focus:outline-none min-h-[400px]',
        },
    },
};

/**
 * Инициализация TipTap редактора
 * @param {Object} options - Опции инициализации
 * @param {string} options.elementId - ID элемента редактора
 * @param {Object} [options.content=''] - Начальный контент
 * @param {string} [options.placeholder='Начните вводить текст заметки...'] - Placeholder
 * @param {Function} [options.onUpdate] - Callback при обновлении контента
 * @param {Function} [options.onSelectionUpdate] - Callback при изменении выделения
 * @param {Function} [options.onImageUploaded] - Callback при загрузке изображения
 * @param {string} [options.type='note'] - Тип редактора ('create-note' | 'note-view')
 * @returns {Editor|null}
 */
export function initNoteEditor(options) {
    const {
        elementId,
        content = '',
        placeholder = 'Начните вводить текст заметки...',
        onUpdate,
        onSelectionUpdate,
        onImageUploaded,
        type = 'note',
    } = options;

    const editorElement = document.querySelector(`#${elementId}`);

    if (!editorElement) {
        console.error(`[NoteEditor] Element #${elementId} not found`);
        return null;
    }

    // Уничтожаем существующий редактор
    if (editorElement._editor) {
        editorElement._editor.destroy();
        editorElement._editor = null;
    }

    // Очищаем элемент
    editorElement.innerHTML = '';

    // Инициализируем модальные окна
    initImageModal();
    initLinkModal();

    // Создаём кастомизированную конфигурацию
    const config = {
        ...DEFAULT_CONFIG,
        element: editorElement,
        content: content,
    };

    // Обновляем placeholder
    config.extensions = config.extensions.map((ext) => {
        if (ext.name === 'placeholder') {
            return Placeholder.configure({ placeholder });
        }
        return ext;
    });

    config.onUpdate = ({ editor }) => {
        const wordCount = editor.state.doc.textContent
            .split(/\s+/)
            .filter((w) => w.length > 0).length;
        const wordCountElement = document.querySelector('[data-word-count]');
        if (wordCountElement) {
            wordCountElement.textContent = `${wordCount} слов`;
        }
        updateToolbarButtons(editor);
        hideAllImageOverlays();
        if (onUpdate) onUpdate(editor);
    };

    config.onSelectionUpdate = ({ editor }) => {
        updateToolbarButtons(editor);
        updateTableControls(editor);

        const { node } = editor.state.selection;
        if (!node || node.type.name !== 'image') {
            hideAllImageOverlays();
        }
        if (onSelectionUpdate) onSelectionUpdate(editor);
    };

    config.onBlur = () => {
        hideAllImageOverlays();
    };

    // Создаём редактор
    const editor = new Editor(config);

    editorElement._editor = editor;

    // Инициализируем тулбар
    setTimeout(() => {
        initToolbarButtons(editor, onImageUploaded);
        updateToolbarButtons(editor);
    }, 0);

    return editor;
}

export function destroyNoteEditor(elementId) {
    const editorElement = document.querySelector(`#${elementId}`);
    if (!editorElement) return;

    if (editorElement._editor) {
        editorElement._editor.destroy();
        editorElement._editor = null;
    }

    editorElement.innerHTML = '';
    editorElement.classList.remove('tiptap');

    ['image-fullscreen-modal', 'link-modal'].forEach((id) => {
        const modal = document.getElementById(id);
        if (modal) modal.remove();
    });

    hideAllImageOverlays();
}

export function getEditorContent(elementId) {
    const editorElement = document.querySelector(`#${elementId}`);
    if (!editorElement || !editorElement._editor) return null;
    return editorElement._editor.getJSON();
}

export function sendContentToLivewire(elementId) {
    const content = getEditorContent(elementId);
    if (content !== null) {
        Livewire.dispatch('editorContent', { content });
    }
}

function initToolbarButtons(editor, onImageUploaded) {
    const buttonActions = {
        bold: () => editor.chain().focus().toggleBold().run(),
        italic: () => editor.chain().focus().toggleItalic().run(),
        underline: () => editor.chain().focus().toggleUnderline().run(),
        strike: () => editor.chain().focus().toggleStrike().run(),
        heading1: () => editor.chain().focus().toggleHeading({ level: 2 }).run(),
        heading2: () => editor.chain().focus().toggleHeading({ level: 3 }).run(),
        bulletList: () => editor.chain().focus().toggleBulletList().run(),
        orderedList: () => editor.chain().focus().toggleOrderedList().run(),
        taskList: () => editor.chain().focus().toggleTaskList().run(),
        undo: () => editor.chain().focus().undo().run(),
        redo: () => editor.chain().focus().redo().run(),
        addRow: () => editor.chain().focus().addRowAfter().run(),
        addCol: () => editor.chain().focus().addColumnAfter().run(),
        deleteRow: () => editor.chain().focus().deleteRow().run(),
        deleteCol: () => editor.chain().focus().deleteColumn().run(),
        mergeCells: () => editor.chain().focus().mergeCells().run(),
        splitCell: () => editor.chain().focus().splitCell().run(),
        alignLeft: () => editor.chain().focus().setTextAlign('left').run(),
        alignCenter: () => editor.chain().focus().setTextAlign('center').run(),
        alignRight: () => editor.chain().focus().setTextAlign('right').run(),
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

    const imageBtn = document.querySelector('[data-editor-action="image"]');
    if (imageBtn) {
        const handler = () => {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/*';
            input.style.display = 'none';
            input.addEventListener('change', async (e) => {
                const file = e.target.files[0];
                if (!file) return;

                try {
                    const formData = new FormData();
                    formData.append('image', file);
                    const response = await fetch('/notes/upload-image', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN':
                                document.querySelector('meta[name="csrf-token"]')?.content,
                            Accept: 'application/json',
                        },
                        body: formData,
                    });
                    if (!response.ok) throw new Error('Ошибка загрузки');
                    const data = await response.json();
                    editor.chain().focus().setImage({ src: data.url, path: data.path }).run();
                    updateToolbarButtons(editor);

                    if (onImageUploaded && typeof onImageUploaded === 'function') {
                        onImageUploaded(data.path);
                    }
                } catch (error) {
                    console.error('[Image Upload] Error:', error);
                    alert('Ошибка при загрузке изображения: ' + error.message);
                }
            });
            input.click();
        };

        imageBtn.addEventListener('click', handler);
        imageBtn._noteEditorHandlerAttached = true;
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

    const tableBtn = document.querySelector('[data-editor-action="table"]');
    if (tableBtn) {
        tableBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (editor.isActive('table')) {
                editor.chain().focus().deleteTable().run();
            } else {
                editor.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run();
            }
            updateToolbarButtons(editor);
        });
    }
}

export {
    closeImageModal,
    closeLinkModal,
    COLOR_PALETTE,
    createPalette,
    getContrastColor,
    hideAllImageOverlays,
    hideAllPalettes,
    initImageModal,
    initLinkModal,
    updateTableControls,
    updateToolbarButtons,
};
