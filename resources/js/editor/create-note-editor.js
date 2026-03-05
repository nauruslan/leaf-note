import { Editor, mergeAttributes, Node } from '@tiptap/core';
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

const COLOR_PALETTE = [
    { name: 'Черный', value: '#000000' },
    { name: 'Серый', value: '#6b7280' },
    { name: 'Красный', value: '#ef4444' },
    { name: 'Оранжевый', value: '#f97316' },
    { name: 'Желтый', value: '#eab308' },
    { name: 'Зеленый', value: '#22c55e' },
    { name: 'Синий', value: '#3b82f6' },
    { name: 'Индиго', value: '#6366f1' },
    { name: 'Фиолетовый', value: '#8b5cf6' },
    { name: 'Розовый', value: '#ec4899' },
    { name: 'Белый', value: '#ffffff' },
];

let lastHighlightColor = null;

function getContrastColor(hex) {
    if (!hex) return '#374151';
    hex = hex.replace('#', '');
    const r = parseInt(hex.substring(0, 2), 16);
    const g = parseInt(hex.substring(2, 4), 16);
    const b = parseInt(hex.substring(4, 6), 16);
    const brightness = (r * 299 + g * 587 + b * 114) / 1000;
    return brightness > 128 ? '#1f2937' : '#f9fafb';
}

function hideAllPalettes() {
    document
        .querySelectorAll('[data-color-palette], [data-highlight-palette]')
        .forEach((palette) => {
            palette.remove();
        });
}

function createPalette(editor, button, type) {
    const paletteAttr = type === 'color' ? 'data-color-palette' : 'data-highlight-palette';
    const existingPalette = document.querySelector(`[${paletteAttr}]`);
    if (existingPalette && existingPalette.parentElement === button.parentElement) {
        existingPalette.remove();
        return;
    }
    hideAllPalettes();

    const palette = document.createElement('div');
    palette.setAttribute(paletteAttr, '');
    palette.className =
        'absolute z-50 mt-1 p-1 bg-white rounded-lg shadow-lg border border-gray-200 flex flex-wrap items-center gap-1 min-w-[150px]';

    COLOR_PALETTE.forEach((color) => {
        const colorBtn = document.createElement('button');
        colorBtn.type = 'button';
        colorBtn.title = color.name;
        colorBtn.className =
            'w-5 h-5 rounded-full border border-black hover:scale-110 transition-transform flex items-center justify-center';
        colorBtn.style.backgroundColor = color.value;
        if (color.value === '#ffffff') {
            colorBtn.style.boxShadow = '0 0 0 1px rgba(0,0,0,0.2)';
        }
        colorBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (type === 'color') {
                editor.chain().focus().setColor(color.value).run();
            } else {
                editor.chain().focus().setHighlight({ color: color.value }).run();
                lastHighlightColor = color.value;
            }
            hideAllPalettes();
            updateToolbarButtons(editor);
        });
        palette.appendChild(colorBtn);
    });

    const closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.title = type === 'color' ? 'Убрать цвет и закрыть' : 'Убрать выделение и закрыть';
    closeBtn.className =
        'w-5 h-5 rounded-full border-2 border-red-500 bg-red-50 text-red-500 hover:bg-red-100 hover:border-red-600 hover:text-red-600 transition-colors flex items-center justify-center ml-1';
    closeBtn.innerHTML = `
    <svg width="10" height="10" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="18" y1="6" x2="6" y2="18"></line>
        <line x1="6" y1="6" x2="18" y2="18"></line>
    </svg>
  `;
    closeBtn.addEventListener('click', (e) => {
        e.stopPropagation();

        if (type === 'color') {
            hideAllPalettes();
            editor.chain().focus().unsetColor().run();
            updateToolbarButtons(editor);
        } else {
            hideAllPalettes();
            editor.chain().focus().unsetHighlight().run();
            lastHighlightColor = null;
            updateToolbarButtons(editor);
        }
    });
    palette.appendChild(closeBtn);

    const parent = button.parentElement;
    if (getComputedStyle(parent).position === 'static') {
        parent.style.position = 'relative';
    }

    parent.appendChild(palette);

    setTimeout(() => {
        document.addEventListener('click', function handler(e) {
            if (!palette.contains(e.target) && !button.contains(e.target)) {
                hideAllPalettes();
                document.removeEventListener('click', handler);
            }
        });
    }, 100);
}

function updateToolbarButtons(editor) {
    const buttons = [
        { action: 'bold', check: () => editor.isActive('bold') },
        { action: 'italic', check: () => editor.isActive('italic') },
        { action: 'underline', check: () => editor.isActive('underline') },
        { action: 'strike', check: () => editor.isActive('strike') },
        { action: 'heading1', check: () => editor.isActive('heading', { level: 2 }) },
        { action: 'heading2', check: () => editor.isActive('heading', { level: 3 }) },
        { action: 'bulletList', check: () => editor.isActive('bulletList') },
        { action: 'orderedList', check: () => editor.isActive('orderedList') },
        { action: 'taskList', check: () => editor.isActive('taskList') },
        { action: 'code', check: () => editor.isActive('codeBlock') },
        { action: 'table', check: () => editor.isActive('table') },
        { action: 'alignLeft', check: () => editor.isActive({ textAlign: 'left' }) },
        { action: 'alignCenter', check: () => editor.isActive({ textAlign: 'center' }) },
        { action: 'alignRight', check: () => editor.isActive({ textAlign: 'right' }) },
        { action: 'highlight', check: () => editor.isActive('highlight') },
        {
            action: 'color',
            check: () => {
                const color = editor.getAttributes('textStyle');
                return !!(color && color.color);
            },
        },
    ];
    buttons.forEach(({ action, check }) => {
        const btn = document.querySelector(`[data-editor-action="${action}"]`);
        if (btn) {
            if (action === 'color' || action === 'highlight') {
                return;
            }

            if (check()) {
                btn.classList.add(
                    'bg-gradient-to-r',
                    'from-indigo-600',
                    'to-purple-600',
                    'text-white',
                );
                btn.classList.remove('text-gray-600', 'hover:text-gray-900', 'hover:bg-gray-200');

                if (action === 'heading1' || action === 'heading2') {
                    const textSpan = btn.querySelector('span');
                    if (textSpan) {
                        textSpan.classList.add(
                            'bg-gradient-to-r',
                            'from-indigo-600',
                            'to-purple-600',
                            'bg-clip-text',
                            'text-white',
                        );
                        textSpan.classList.remove('text-gray-600');
                    }
                }
            } else {
                btn.classList.remove(
                    'bg-gradient-to-r',
                    'from-indigo-600',
                    'to-purple-600',
                    'text-white',
                );
                btn.classList.add('text-gray-600', 'hover:text-gray-900', 'hover:bg-gray-200');

                if (action === 'heading1' || action === 'heading2') {
                    const textSpan = btn.querySelector('span');
                    if (textSpan) {
                        textSpan.classList.remove(
                            'bg-gradient-to-r',
                            'from-indigo-600',
                            'to-purple-600',
                            'bg-clip-text',
                            'text-white',
                        );
                        textSpan.classList.add('text-gray-600');
                    }
                }
            }
        }
    });

    const colorBtn = document.querySelector('[data-editor-action="color"]');
    if (colorBtn) {
        const color = editor.getAttributes('textStyle').color;
        if (color) {
            colorBtn.style.backgroundColor = color;
            colorBtn.style.color = getContrastColor(color);
            colorBtn.classList.add('border', 'border-gray-300');
            colorBtn.querySelector('i')?.classList.add('hidden');
        } else {
            colorBtn.style.backgroundColor = '';
            colorBtn.style.color = '';
            colorBtn.classList.remove('border', 'border-gray-300');
            colorBtn.querySelector('i')?.classList.remove('hidden');
        }
    }

    const highlightBtn = document.querySelector('[data-editor-action="highlight"]');
    if (highlightBtn) {
        const highlightColor = lastHighlightColor;

        if (highlightColor) {
            highlightBtn.style.backgroundColor = highlightColor;
            highlightBtn.style.color = getContrastColor(highlightColor);
            highlightBtn.classList.add('border', 'border-gray-300');
            highlightBtn.querySelector('i')?.classList.add('hidden');
        } else {
            highlightBtn.style.backgroundColor = '';
            highlightBtn.style.color = '';
            highlightBtn.classList.remove('border', 'border-gray-300');
            highlightBtn.querySelector('i')?.classList.remove('hidden');
        }
    }

    updateTableControls(editor);
}

function updateTableControls(editor) {
    const tableControls = document.querySelector('.table-controls');
    if (!tableControls) return;
    const actions = ['addRow', 'addCol', 'deleteRow', 'deleteCol', 'mergeCells', 'splitCell'];

    if (editor.isActive('table')) {
        tableControls.classList.remove('hidden');
        actions.forEach((action) => {
            const btn = document.querySelector(`[data-editor-action="${action}"]`);
            if (btn) {
                btn.classList.remove('opacity-50', 'cursor-not-allowed');
                btn.disabled = false;
            }
        });
    } else {
        tableControls.classList.add('hidden');
        actions.forEach((action) => {
            const btn = document.querySelector(`[data-editor-action="${action}"]`);
            if (btn) {
                btn.classList.add('opacity-50', 'cursor-not-allowed');
                btn.disabled = true;
            }
        });
    }
}

let activeImageWrapper = null;

function openImageModal(imageSrc, imageAlt) {
    const modal = document.getElementById('image-fullscreen-modal');
    if (!modal) return;

    const modalImg = modal.querySelector('.image-modal-content img');
    const modalCaption = modal.querySelector('.image-modal-caption');

    if (modalImg) {
        modalImg.src = imageSrc;
        modalImg.alt = imageAlt || '';
    }
    if (modalCaption) {
        modalCaption.textContent = imageAlt || '';
        modalCaption.style.display = imageAlt ? 'block' : 'none';
    }

    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeImageModal() {
    const modal = document.getElementById('image-fullscreen-modal');
    if (!modal) return;

    modal.classList.remove('active');
    document.body.style.overflow = '';
}

function initImageModal() {
    if (document.getElementById('image-fullscreen-modal')) return;

    const modal = document.createElement('div');
    modal.id = 'image-fullscreen-modal';
    modal.className = 'image-modal';
    modal.innerHTML = `
        <div class="image-modal-content">
            <button class="image-modal-close" aria-label="Закрыть">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
            <img src="" alt="">
            <div class="image-modal-caption"></div>
        </div>
    `;

    document.body.appendChild(modal);

    modal.querySelector('.image-modal-close').addEventListener('click', (e) => {
        e.stopPropagation();
        closeImageModal();
    });

    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeImageModal();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.classList.contains('active')) {
            closeImageModal();
        }
    });
}

let linkModalCallback = null;

function openLinkModal(editor) {
    const modal = document.getElementById('link-modal');
    const input = document.getElementById('link-input');
    if (!modal || !input) return;

    const currentLink = editor.getAttributes('link').href;
    input.value = currentLink || '';
    input.placeholder = currentLink ? 'Измените ссылку' : 'https://example.com';

    modal.classList.add('active');
    document.body.style.overflow = 'hidden';

    setTimeout(() => input.focus(), 100);

    linkModalCallback = editor;
}

function closeLinkModal() {
    const modal = document.getElementById('link-modal');
    if (!modal) return;

    modal.classList.remove('active');
    document.body.style.overflow = '';
    linkModalCallback = null;
}

function handleLinkModalAction(action) {
    const modal = document.getElementById('link-modal');
    const input = document.getElementById('link-input');
    if (!modal || !input || !linkModalCallback) return;

    const editor = linkModalCallback;

    if (action === 'ok') {
        const url = input.value.trim();
        if (url) {
            let finalUrl = url;
            if (!/^https?:\/\//i.test(url)) {
                finalUrl = 'https://' + url;
            }
            editor.chain().focus().setLink({ href: finalUrl }).run();
        } else {
            editor.chain().focus().unsetLink().run();
        }
    }

    updateToolbarButtons(editor);
    closeLinkModal();
}

function initLinkModal() {
    if (document.getElementById('link-modal')) {
        const modal = document.getElementById('link-modal');

        modal.querySelectorAll('[data-link-action]').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const action = btn.getAttribute('data-link-action');
                handleLinkModalAction(action);
            });
        });

        const input = document.getElementById('link-input');
        if (input) {
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    handleLinkModalAction('ok');
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    handleLinkModalAction('cancel');
                }
            });
        }

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                handleLinkModalAction('cancel');
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.classList.contains('active')) {
                handleLinkModalAction('cancel');
            }
        });
    }
}

const CustomImage = Node.create({
    name: 'image',
    group: 'block',
    draggable: true,
    selectable: true,
    inline: false,

    addAttributes() {
        return {
            src: {
                default: null,
            },
            alt: {
                default: null,
            },
            title: {
                default: null,
            },
        };
    },

    parseHTML() {
        return [
            {
                tag: 'img[src]',
            },
        ];
    },

    renderHTML({ HTMLAttributes }) {
        return [
            'div',
            {
                class: 'image-node-wrapper',
                style: 'position: relative; display: inline-block; margin: 1rem 0;',
            },
            [
                'img',
                mergeAttributes(HTMLAttributes, {
                    class: 'rounded-lg max-w-full h-auto shadow-md',
                }),
            ],
            [
                'div',
                {
                    class: 'image-overlay',
                    style: 'position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center; gap: 1rem;',
                },
                [
                    'button',
                    {
                        type: 'button',
                        class: 'image-action-btn view-btn',
                        'data-action': 'view-image',
                        title: 'Просмотр изображения',
                        'aria-label': 'Просмотр изображения',
                    },
                    [
                        'svg',
                        {
                            width: '24',
                            height: '24',
                            viewBox: '0 0 24 24',
                            fill: 'none',
                            stroke: 'currentColor',
                            'stroke-width': '2',
                            'stroke-linecap': 'round',
                            'stroke-linejoin': 'round',
                        },
                        ['path', { d: 'M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z' }],
                        ['circle', { cx: '12', cy: '12', r: '3' }],
                    ],
                ],
                [
                    'button',
                    {
                        type: 'button',
                        class: 'image-action-btn delete-btn',
                        'data-action': 'delete-image',
                        title: 'Удалить изображение',
                        'aria-label': 'Удалить изображение',
                    },
                    [
                        'svg',
                        {
                            width: '24',
                            height: '24',
                            viewBox: '0 0 24 24',
                            fill: 'none',
                            stroke: 'currentColor',
                            'stroke-width': '2',
                            'stroke-linecap': 'round',
                            'stroke-linejoin': 'round',
                        },
                        ['polyline', { points: '3 6 5 6 21 6' }],
                        [
                            'path',
                            {
                                d: 'M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2',
                            },
                        ],
                        ['line', { x1: '10', y1: '11', x2: '10', y2: '17' }],
                        ['line', { x1: '14', y1: '11', x2: '14', y2: '17' }],
                    ],
                ],
            ],
        ];
    },

    addCommands() {
        return {
            setImage:
                (options) =>
                ({ commands }) => {
                    return commands.insertContent({
                        type: this.name,
                        attrs: options,
                    });
                },
        };
    },

    addNodeView() {
        return ({ node, HTMLAttributes, getPos, editor }) => {
            const wrapper = document.createElement('div');
            wrapper.className = 'image-node-wrapper';
            wrapper.style.position = 'relative';
            wrapper.style.display = 'inline-block';
            wrapper.style.margin = '1rem 0';
            wrapper.style.lineHeight = '0';

            const img = document.createElement('img');
            img.src = node.attrs.src;
            img.alt = node.attrs.alt || '';
            img.title = node.attrs.title || '';
            img.className = 'rounded-lg max-w-full h-auto shadow-md cursor-pointer';
            img.style.display = 'block';

            const overlay = document.createElement('div');
            overlay.className = 'image-overlay';
            overlay.style.position = 'absolute';
            overlay.style.top = '0';
            overlay.style.left = '0';
            overlay.style.right = '0';
            overlay.style.bottom = '0';
            overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
            overlay.style.borderRadius = '0.5rem';
            overlay.style.display = 'flex';
            overlay.style.alignItems = 'center';
            overlay.style.justifyContent = 'center';
            overlay.style.gap = '1rem';
            overlay.style.opacity = '0';
            overlay.style.visibility = 'hidden';
            overlay.style.transition = 'all 0.2s ease';
            overlay.style.zIndex = '100';
            overlay.style.cursor = 'pointer';
            overlay.style.pointerEvents = 'none';

            const viewBtn = document.createElement('button');
            viewBtn.type = 'button';
            viewBtn.className = 'image-action-btn view-btn';
            viewBtn.style.background = '#ffffff';
            viewBtn.style.border = '2px solid #6366f1';
            viewBtn.style.borderRadius = '50%';
            viewBtn.style.width = '48px';
            viewBtn.style.height = '48px';
            viewBtn.style.display = 'flex';
            viewBtn.style.alignItems = 'center';
            viewBtn.style.justifyContent = 'center';
            viewBtn.style.cursor = 'pointer';
            viewBtn.style.boxShadow = '0 4px 10px rgba(0,0,0,0.3)';
            viewBtn.style.padding = '0';
            viewBtn.style.color = '#6366f1';
            viewBtn.style.transition = 'all 0.2s ease';
            viewBtn.style.zIndex = '101';
            viewBtn.style.pointerEvents = 'auto';
            viewBtn.title = 'Просмотр изображения';
            viewBtn.setAttribute('aria-label', 'Просмотр изображения');
            viewBtn.innerHTML = `
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
            `;

            viewBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                e.preventDefault();
                openImageModal(node.attrs.src, node.attrs.alt);
            });

            const deleteBtn = document.createElement('button');
            deleteBtn.type = 'button';
            deleteBtn.className = 'image-action-btn delete-btn';
            deleteBtn.style.background = '#ffffff';
            deleteBtn.style.border = '2px solid #ef4444';
            deleteBtn.style.borderRadius = '50%';
            deleteBtn.style.width = '48px';
            deleteBtn.style.height = '48px';
            deleteBtn.style.display = 'flex';
            deleteBtn.style.alignItems = 'center';
            deleteBtn.style.justifyContent = 'center';
            deleteBtn.style.cursor = 'pointer';
            deleteBtn.style.boxShadow = '0 4px 10px rgba(0,0,0,0.3)';
            deleteBtn.style.padding = '0';
            deleteBtn.style.color = '#ef4444';
            deleteBtn.style.transition = 'all 0.2s ease';
            deleteBtn.style.zIndex = '101';
            deleteBtn.style.pointerEvents = 'auto';
            deleteBtn.title = 'Удалить изображение';
            deleteBtn.setAttribute('aria-label', 'Удалить изображение');
            deleteBtn.innerHTML = `
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    <line x1="10" y1="11" x2="10" y2="17"></line>
                    <line x1="14" y1="11" x2="14" y2="17"></line>
                </svg>
            `;

            deleteBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                e.preventDefault();
                if (typeof getPos === 'function') {
                    const pos = getPos();
                    editor
                        .chain()
                        .focus()
                        .deleteRange({ from: pos, to: pos + 1 })
                        .run();
                }

                activeImageWrapper = null;
            });

            img.addEventListener('click', (e) => {
                e.stopPropagation();
                if (activeImageWrapper && activeImageWrapper !== wrapper) {
                    const prevOverlay = activeImageWrapper.querySelector('.image-overlay');
                    if (prevOverlay) {
                        prevOverlay.style.opacity = '0';
                        prevOverlay.style.visibility = 'hidden';
                        prevOverlay.style.pointerEvents = 'none';
                        prevOverlay.classList.remove('active');
                    }
                }

                overlay.style.opacity = '1';
                overlay.style.visibility = 'visible';
                overlay.style.pointerEvents = 'auto';
                overlay.classList.add('active');

                activeImageWrapper = wrapper;

                if (typeof getPos === 'function') {
                    const pos = getPos();
                    editor.chain().focus().setNodeSelection(pos).run();
                }
            });

            overlay.addEventListener('click', (e) => {
                e.stopPropagation();
            });

            wrapper.appendChild(img);
            wrapper.appendChild(overlay);
            overlay.appendChild(viewBtn);
            overlay.appendChild(deleteBtn);

            return {
                dom: wrapper,
                contentDOM: null,
            };
        };
    },
});

function hideAllImageOverlays() {
    document.querySelectorAll('.image-overlay').forEach((overlay) => {
        overlay.style.opacity = '0';
        overlay.style.visibility = 'hidden';
        overlay.style.pointerEvents = 'none';
        overlay.classList.remove('active');
    });
    activeImageWrapper = null;
}

function initToolbarButtons(editor) {
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
        code: () => editor.chain().focus().toggleCodeBlock().run(),
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
            btn.addEventListener('click', () => {
                handler();
                updateToolbarButtons(editor);
            });
        }
    });

    const linkBtn = document.querySelector('[data-editor-action="link"]');
    if (linkBtn) {
        linkBtn.addEventListener('click', () => {
            openLinkModal(editor);
        });
    }

    const imageBtn = document.querySelector('[data-editor-action="image"]');
    if (imageBtn) {
        imageBtn.addEventListener('click', async () => {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/*';

            const editorContainer = document.querySelector('#editor').closest('div');

            input.onchange = async (e) => {
                const file = e.target.files[0];
                if (!file) return;
                if (!file.type.match('image.*')) {
                    alert('Пожалуйста, выберите изображение');
                    return;
                }
                if (file.size > 5 * 1024 * 1024) {
                    alert('Размер изображения не должен превышать 5МБ');
                    return;
                }
                try {
                    const loadingEl = document.createElement('div');
                    loadingEl.className =
                        'absolute inset-0 bg-white/80 flex items-center justify-center z-10 rounded-lg';
                    loadingEl.innerHTML =
                        '<div class="text-gray-500">Загрузка изображения...</div>';
                    editorContainer.style.position = 'relative';
                    editorContainer.appendChild(loadingEl);
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
                    loadingEl.remove();
                    if (!response.ok) throw new Error('Ошибка загрузки');
                    const data = await response.json();
                    editor.chain().focus().setImage({ src: data.url }).run();
                    updateToolbarButtons(editor);
                } catch (error) {
                    console.error('Ошибка загрузки изображения:', error);
                    alert('Ошибка при загрузке изображения: ' + error.message);
                    const loadingEl = editorContainer.querySelector('.absolute.inset-0');
                    if (loadingEl) loadingEl.remove();
                }
            };
            input.click();
        });
    }

    const colorBtn = document.querySelector('[data-editor-action="color"]');
    if (colorBtn) {
        colorBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            createPalette(editor, colorBtn, 'color');
        });
    }

    const highlightBtn = document.querySelector('[data-editor-action="highlight"]');
    if (highlightBtn) {
        highlightBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            createPalette(editor, highlightBtn, 'highlight');
        });
    }

    document.addEventListener('click', (e) => {
        if (
            !e.target.closest('[data-color-palette]') &&
            !e.target.closest('[data-highlight-palette]')
        ) {
            hideAllPalettes();
        }
        if (!e.target.closest('#editor')) {
            hideAllImageOverlays();
        }
    });

    const tableBtn = document.querySelector('[data-editor-action="table"]');
    if (tableBtn) {
        tableBtn.addEventListener('click', () => {
            if (editor.isActive('table')) {
                editor.chain().focus().deleteTable().run();
            } else {
                editor
                    .chain()
                    .focus()
                    .insertTable({ rows: 3, cols: 3, withHeaderRow: true })
                    .updateAttributes('table', { style: 'width: 60%; table-layout: fixed;' })
                    .run();
                setTimeout(() => {
                    const firstCell = document.querySelector('#editor table td, #editor table th');
                    if (firstCell) {
                        firstCell.focus();
                        const range = document.createRange();
                        const sel = window.getSelection();
                        range.selectNodeContents(firstCell);
                        range.collapse(true);
                        sel.removeAllRanges();
                        sel.addRange(range);
                    }
                    updateToolbarButtons(editor);
                }, 50);
            }
        });
    }
}

function getEditorContent() {
    const editorElement = document.querySelector('#editor');
    if (!editorElement || !editorElement._editor) return null;
    return editorElement._editor.getJSON();
}

function sendContentToLivewire() {
    const content = getEditorContent();

    if (content !== null) {
        Livewire.dispatch('editorContent', { content: content });
    }
}

export function destroyCreateNoteEditor() {
    const editorElement = document.querySelector('#editor');
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

    if (typeof activeImageWrapper !== 'undefined') activeImageWrapper = null;
    if (typeof linkModalCallback !== 'undefined') linkModalCallback = null;
}

export function initCreateNoteEditor() {
    const editorElement = document.querySelector('#editor');

    if (!editorElement) {
        return null;
    }

    if (editorElement._editor || editorElement.querySelector('.ProseMirror')) {
        destroyCreateNoteEditor();
    }

    editorElement.innerHTML = '';

    initImageModal();
    initLinkModal();

    const editor = new Editor({
        element: editorElement,
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
        content: '',
        editorProps: {
            attributes: {
                class: 'prose prose-indigo max-w-none focus:outline-none text-gray-700 min-h-[400px]',
            },
        },
        onUpdate: ({ editor }) => {
            const wordCount = editor.state.doc.textContent
                .split(/\s+/)
                .filter((w) => w.length > 0).length;
            const wordCountElement = document.querySelector('[data-word-count]');
            if (wordCountElement) {
                wordCountElement.textContent = `${wordCount} слов`;
            }
            updateToolbarButtons(editor);
        },
    });

    editorElement._editor = editor;

    editor.on('selectionUpdate', () => {
        updateToolbarButtons(editor);
        updateTableControls(editor);

        const { node } = editor.state.selection;
        if (!node || node.type.name !== 'image') {
            hideAllImageOverlays();
        }
    });

    editor.on('blur', () => {
        hideAllImageOverlays();
    });

    editor.on('update', () => {
        hideAllImageOverlays();
    });

    initToolbarButtons(editor);
    updateToolbarButtons(editor);

    return editor;
}

document.addEventListener('livewire:init', () => {
    Livewire.on('getEditorContent', () => {
        sendContentToLivewire();
    });
    Livewire.on('destroyEditor', () => {
        console.log('🧹 destroyEditor event received');
        setTimeout(() => {
            if (typeof destroyCreateNoteEditor === 'function') {
                destroyCreateNoteEditor();
            }
        }, 10);
    });
});
