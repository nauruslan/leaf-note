import { Node } from '@tiptap/core';

let activeImageWrapper = null;
let linkModalCallback = null;

export const COLOR_PALETTE = [
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

export function openImageModal(imageSrc, imageAlt) {
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

export function closeImageModal() {
    const modal = document.getElementById('image-fullscreen-modal');
    if (!modal) return;

    modal.classList.remove('active');
    document.body.style.overflow = '';
}

export function initImageModal() {
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

export function openLinkModal(editor) {
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

export function closeLinkModal() {
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

export function initLinkModal() {
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

export function updateToolbarButtons(editor) {
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
            if (action === 'color' || action === 'highlight') return;

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
        const highlightColor = editor.getAttributes('highlight')?.color || null;
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

export function updateTableControls(editor) {
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

export function hideAllImageOverlays() {
    document.querySelectorAll('.image-overlay').forEach((overlay) => {
        overlay.style.opacity = '0';
        overlay.style.visibility = 'hidden';
        overlay.style.pointerEvents = 'none';
        overlay.classList.remove('active');
    });
    activeImageWrapper = null;
}

export function getContrastColor(hex) {
    if (!hex) return '#374151';
    hex = hex.replace('#', '');
    const r = parseInt(hex.substring(0, 2), 16);
    const g = parseInt(hex.substring(2, 4), 16);
    const b = parseInt(hex.substring(4, 6), 16);
    const brightness = (r * 299 + g * 587 + b * 114) / 1000;
    return brightness > 128 ? '#1f2937' : '#f9fafb';
}

export function hideAllPalettes() {
    document
        .querySelectorAll('[data-color-palette], [data-highlight-palette]')
        .forEach((palette) => {
            palette.remove();
        });
}

export function createPalette(editor, button, type) {
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
                window.lastHighlightColor = color.value;
            }
            hideAllPalettes();
            requestAnimationFrame(() => updateToolbarButtons(editor));
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

            // Сбрасываем стили кнопки color сразу
            const colorBtn = document.querySelector('[data-editor-action="color"]');
            if (colorBtn) {
                colorBtn.style.backgroundColor = '';
                colorBtn.style.color = '';
                colorBtn.classList.remove('border', 'border-gray-300');
                colorBtn.querySelector('i')?.classList.remove('hidden');
            }
        } else {
            hideAllPalettes();
            editor.chain().focus().unsetHighlight().run();
            window.lastHighlightColor = null;

            // Сбрасываем стили кнопки highlight сразу
            const highlightBtn = document.querySelector('[data-editor-action="highlight"]');
            if (highlightBtn) {
                highlightBtn.style.backgroundColor = '';
                highlightBtn.style.color = '';
                highlightBtn.classList.remove('border', 'border-gray-300');
                highlightBtn.querySelector('i')?.classList.remove('hidden');
            }
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

export const CustomImage = Node.create({
    name: 'image',
    group: 'block',
    draggable: true,
    selectable: true,
    inline: false,

    addAttributes() {
        return {
            src: { default: null },
            alt: { default: null },
            title: { default: null },
            path: { default: null },
        };
    },

    parseHTML() {
        return [{ tag: 'img[src]' }];
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
                {
                    ...HTMLAttributes,
                    class: 'rounded-lg max-w-full h-auto shadow-md cursor-pointer',
                },
            ],
            createImageOverlay(),
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
        return ({ node, getPos, editor }) => {
            const wrapper = document.createElement('div');
            wrapper.className = 'image-node-wrapper';
            wrapper.style.cssText =
                'position: relative; display: inline-block; margin: 1rem 0; line-height: 0;';

            const img = document.createElement('img');
            img.src = node.attrs.src;
            img.alt = node.attrs.alt || '';
            img.title = node.attrs.title || '';
            img.className = 'rounded-lg max-w-full h-auto shadow-md cursor-pointer';
            img.style.display = 'block';

            const overlay = createImageOverlayElement();
            overlay.style.cssText = `
                position: absolute; top: 0; left: 0; right: 0; bottom: 0;
                background-color: rgba(0, 0, 0, 0.5); border-radius: 0.5rem;
                display: flex; align-items: center; justify-content: center; gap: 1rem;
                opacity: 0; visibility: hidden; transition: all 0.2s ease;
                z-index: 100; cursor: pointer; pointer-events: none;
            `;

            const viewBtn = createImageViewButton(node.attrs.src, node.attrs.alt);
            const deleteBtn = createImageDeleteButton(node, getPos, editor);

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

function createImageOverlay() {
    return [
        'div',
        {
            class: 'image-overlay',
            style: 'position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center; gap: 1rem;',
        },
        createViewButtonHTML(),
        createDeleteButtonHTML(),
    ];
}

function createImageOverlayElement() {
    const overlay = document.createElement('div');
    overlay.className = 'image-overlay';
    return overlay;
}

function createImageViewButton(imageSrc, imageAlt) {
    const viewBtn = document.createElement('button');
    viewBtn.type = 'button';
    viewBtn.className = 'image-action-btn view-btn';
    viewBtn.style.cssText = `
        background: #ffffff; border: 2px solid #6366f1; border-radius: 50%;
        width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;
        cursor: pointer; box-shadow: 0 4px 10px rgba(0,0,0,0.3); padding: 0;
        color: #6366f1; transition: all 0.2s ease; z-index: 101; pointer-events: auto;
    `;
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
        openImageModal(imageSrc, imageAlt);
    });

    return viewBtn;
}

function createImageDeleteButton(node, getPos, editor) {
    const deleteBtn = document.createElement('button');
    deleteBtn.type = 'button';
    deleteBtn.className = 'image-action-btn delete-btn';
    deleteBtn.style.cssText = `
        background: #ffffff; border: 2px solid #ef4444; border-radius: 50%;
        width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;
        cursor: pointer; box-shadow: 0 4px 10px rgba(0,0,0,0.3); padding: 0;
        color: #ef4444; transition: all 0.2s ease; z-index: 101; pointer-events: auto;
    `;
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

        // Удаляем изображение из редактора
        // Мягкое удаление (softDeleteImage) вызывается автоматически в syncImageState при изменении контента
        if (typeof getPos === 'function') {
            const pos = getPos();
            editor
                .chain()
                .focus()
                .deleteRange({ from: pos, to: pos + 1 })
                .run();
        }
    });

    return deleteBtn;
}

/**
 * Мягкое удаление изображения - помечает на удаление, но не удаляет физически
 * @param {string} path - Путь к изображению
 */
export function softDeleteImage(path) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    fetch('/notes/soft-delete-image', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            Accept: 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ path }),
    }).catch((error) => {
        // Игнорируем 429 (rate limit) ошибки, чтобы не блокировать JS
        if (error.status === 429) {
            console.warn('[ImageSoftDelete] Rate limited, skipping:', path);
            return;
        }
        console.error('[ImageSoftDelete] Error:', error);
    });
}

/**
 * Восстановление изображения из списка на удаление
 * @param {string} path - Путь к изображению
 */
export function restoreImage(path) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    fetch('/notes/restore-image', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            Accept: 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ path }),
    })
        .then((response) => {
            // Игнорируем 429 (rate limit) ошибки, чтобы не блокировать JS
            if (response.status === 429) {
                console.warn('[ImageRestore] Rate limited, skipping:', path);
                return;
            }
            if (!response.ok) {
                console.error('[ImageRestore] Error:', response.status);
            }
        })
        .catch((error) => {
            console.error('[ImageRestore] Error:', error);
        });
}

/**
 * Выполнить фактическое удаление всех помеченных изображений
 */
export function executePendingDeletion() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    return fetch('/notes/execute-deletion', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            Accept: 'application/json',
            'Content-Type': 'application/json',
        },
    }).catch((error) => {
        console.error('[ExecuteDeletion] Error:', error);
    });
}

function createViewButtonHTML() {
    return [
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
    ];
}

function createDeleteButtonHTML() {
    return [
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
    ];
}
