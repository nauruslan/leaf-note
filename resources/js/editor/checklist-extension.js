import { Extension, mergeAttributes, Node } from '@tiptap/core';
import { Plugin, PluginKey } from '@tiptap/pm/state';

export const AddChecklistButton = Extension.create({
    name: 'addChecklistButton',

    addOptions() {
        return {
            buttonLabel: 'Добавить задачу',
        };
    },

    addProseMirrorPlugins() {
        const { buttonLabel } = this.options;

        return [
            new Plugin({
                key: new PluginKey('addChecklistButton'),
                props: {
                    decorations: (state) => {
                        const { doc } = state;
                        const decorations = [];

                        if (
                            doc.childCount === 0 ||
                            (doc.childCount === 1 &&
                                doc.firstChild?.type.name === 'checklist' &&
                                doc.firstChild.childCount === 0)
                        ) {
                            return null;
                        }

                        return decorations.length > 0 ? decorations : null;
                    },
                },
            }),
        ];
    },
});

export const ChecklistNavigation = Extension.create({
    name: 'checklistNavigation',
    priority: 1000,

    addKeyboardShortcuts() {
        return {
            ArrowUp: ({ editor }) => {
                const { state } = editor;
                const { selection } = state;
                const { $from } = selection;

                const currentNode = $from.node();
                if (currentNode.type.name !== 'checklistItem') return false;

                const parent = $from.node($from.depth - 1);
                if (!parent || parent.type.name !== 'checklist') return false;

                const firstChild = parent.firstChild;
                if (firstChild !== currentNode) return false;

                if ($from.parentOffset === 0) return true;
                return false;
            },

            ArrowLeft: ({ editor }) => {
                const { state } = editor;
                const { selection } = state;
                const { $from } = selection;

                const currentNode = $from.node();
                if (currentNode.type.name !== 'checklistItem') return false;

                const parent = $from.node($from.depth - 1);
                if (!parent || parent.type.name !== 'checklist') return false;

                const firstChild = parent.firstChild;
                if (firstChild !== currentNode) return false;

                if ($from.parentOffset === 0) return true;
                return false;
            },

            ArrowDown: ({ editor }) => {
                const { state } = editor;
                const { selection } = state;
                const { $from } = selection;

                const currentNode = $from.node();
                if (currentNode.type.name !== 'checklistItem') return false;

                const parent = $from.node($from.depth - 1);
                if (!parent || parent.type.name !== 'checklist') return false;

                const lastChild = parent.lastChild;
                if (lastChild !== currentNode) return false;

                const currentContent = $from.parent;
                if ($from.parentOffset === currentContent.content.size) return true;
                return false;
            },

            ArrowRight: ({ editor }) => {
                const { state } = editor;
                const { selection } = state;
                const { $from } = selection;

                const currentNode = $from.node();
                if (currentNode.type.name !== 'checklistItem') return false;

                const parent = $from.node($from.depth - 1);
                if (!parent || parent.type.name !== 'checklist') return false;

                const lastChild = parent.lastChild;
                if (lastChild !== currentNode) return false;

                const currentContent = $from.parent;
                if ($from.parentOffset === currentContent.content.size) return true;
                return false;
            },
        };
    },

    addProseMirrorPlugins() {
        const pluginKey = new PluginKey('checklistNavigation');

        const findAllChecklistRanges = (doc) => {
            const ranges = [];
            doc.descendants((node, pos) => {
                if (node.type.name === 'checklist') {
                    ranges.push({ from: pos, to: pos + node.nodeSize });
                }
            });
            return ranges;
        };

        return [
            new Plugin({
                key: pluginKey,
                filterTransaction: (tr, state) => {
                    if (tr.selectionSet) {
                        const newSelection = tr.selection;
                        const doc = tr.doc || state.doc;
                        const ranges = findAllChecklistRanges(doc);

                        if (ranges.length === 0) return true;

                        const { from, to } = newSelection;
                        const isInside = ranges.some(
                            (range) => from >= range.from && to <= range.to,
                        );

                        if (!isInside) return false;
                    }
                    return true;
                },
                appendTransaction: (transactions, oldState, newState) => {
                    const hasSelectionChange = transactions.some((tr) => tr.selectionSet);
                    if (!hasSelectionChange) return null;

                    const ranges = findAllChecklistRanges(newState.doc);
                    if (ranges.length === 0) return null;

                    const { from, to } = newState.selection;
                    const isInside = ranges.some((range) => from >= range.from && to <= range.to);
                    if (!isInside) {
                        const firstChecklist = ranges[0];
                        let firstItemPos = null;
                        newState.doc.descendants((node, pos) => {
                            if (
                                node.type.name === 'checklistItem' &&
                                pos >= firstChecklist.from &&
                                pos < firstChecklist.to
                            ) {
                                firstItemPos = pos + 1;
                                return false;
                            }
                        });
                        if (firstItemPos !== null) {
                            const tr = newState.tr;
                            return tr.setSelection(
                                newState.selection.constructor.near(
                                    newState.doc.resolve(firstItemPos),
                                ),
                            );
                        }
                    }
                    return null;
                },
            }),
        ];
    },
});

export const Checklist = Node.create({
    name: 'checklist',
    group: 'block',
    content: 'checklistItem*',
    draggable: true,

    parseHTML() {
        return [{ tag: 'div[data-type="checklist"]' }];
    },

    renderHTML({ HTMLAttributes }) {
        return [
            'div',
            mergeAttributes(HTMLAttributes, {
                'data-type': 'checklist',
                class: 'checklist-container',
            }),
            0,
        ];
    },

    addCommands() {
        return {
            insertChecklist:
                () =>
                ({ commands }) =>
                    commands.insertContent({
                        type: this.name,
                        content: [],
                    }),
            appendChecklistItem:
                () =>
                ({ commands, editor }) => {
                    const { state } = editor;
                    let checklistPos = null;
                    state.doc.descendants((node, pos) => {
                        if (node.type.name === 'checklist') {
                            checklistPos = pos;
                            return false;
                        }
                    });
                    if (checklistPos === null) {
                        return commands.insertChecklist();
                    }
                    const checklistNode = state.doc.nodeAt(checklistPos);
                    const insertPos = checklistPos + checklistNode.nodeSize - 1;
                    return commands.insertContentAt(insertPos, {
                        type: 'checklistItem',
                        attrs: { checked: false },
                        content: [{ type: 'paragraph', content: [] }],
                    });
                },
        };
    },

    addNodeView() {
        return ({ editor }) => {
            const wrapper = document.createElement('div');
            wrapper.setAttribute('data-type', 'checklist');
            wrapper.className = 'checklist-container-wrapper';
            wrapper.style.cssText = 'display: flex; flex-direction: column; gap: 0.5rem;';

            const container = document.createElement('div');
            container.setAttribute('data-type', 'checklist');
            container.className = 'checklist-container';
            container.style.cssText = 'display: flex; flex-direction: column; gap: 0.5rem;';

            const addTaskBtn = document.createElement('button');
            addTaskBtn.type = 'button';
            addTaskBtn.className = 'add-checklist-task-btn-inline';
            addTaskBtn.innerHTML = `
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="pointer-events: none;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Добавить задачу</span>
            `;
            addTaskBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                editor.commands.appendChecklistItem();
                setTimeout(() => {
                    editor.commands.focus();
                    const { state } = editor;
                    let lastItemPos = null;
                    state.doc.descendants((node, pos) => {
                        if (node.type.name === 'checklistItem') {
                            lastItemPos = pos + node.nodeSize - 2;
                        }
                    });
                    if (lastItemPos !== null) {
                        editor.commands.setTextSelection(lastItemPos);
                    }
                }, 50);
            });

            wrapper.appendChild(container);
            wrapper.appendChild(addTaskBtn);

            return { dom: wrapper, contentDOM: container };
        };
    },
});

export const ChecklistItem = Node.create({
    name: 'checklistItem',
    group: 'block',
    content: 'paragraph',
    defining: true,
    isolating: true,

    addAttributes() {
        return {
            checked: {
                default: false,
                parseHTML: (element) => element.getAttribute('data-checked') === 'true',
                renderHTML: (attributes) => ({
                    'data-checked': attributes.checked ? 'true' : 'false',
                }),
            },
        };
    },

    parseHTML() {
        return [{ tag: 'div[data-type="checklist-item"]' }];
    },

    renderHTML({ HTMLAttributes, node }) {
        const isChecked = node.attrs.checked;
        return [
            'div',
            mergeAttributes(HTMLAttributes, {
                'data-type': 'checklist-item',
                'data-checked': isChecked ? 'true' : 'false',
                class: `checklist-item ${isChecked ? 'checked' : ''}`,
            }),
            [
                'div',
                {
                    class: 'checklist-item-inner',
                    style: 'display: flex; align-items: center; gap: 0.75rem;',
                },
                [
                    'div',
                    {
                        class: 'checklist-checkbox',
                        'data-action': 'toggle-check',
                        style: `flex-shrink: 0; width: 1.25rem; height: 1.25rem; border: 2px solid ${isChecked ? '#6366f1' : '#d1d5db'}; border-radius: 0.375rem; background: ${isChecked ? 'linear-gradient(to right, #6366f1, #8b5cf6)' : 'white'}; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);`,
                    },
                    isChecked
                        ? [
                              'svg',
                              {
                                  width: '14',
                                  height: '14',
                                  viewBox: '0 0 24 24',
                                  fill: 'none',
                                  stroke: 'white',
                                  'stroke-width': '3',
                                  style: 'pointer-events: none;',
                              },
                              ['polyline', { points: '20 6 9 17 4 12' }],
                          ]
                        : [],
                ],
                [
                    'div',
                    {
                        class: 'checklist-item-content',
                        style: 'display: inline-block; max-width: 100%; position: relative;',
                        contenteditable: 'true',
                    },
                    0,
                ],
            ],
        ];
    },

    addCommands() {
        return {
            toggleChecklistItemChecked:
                () =>
                ({ commands, state }) => {
                    const { selection } = state;
                    const { $from } = selection;
                    const checklistItemNode = $from.node($from.depth);
                    if (!checklistItemNode || checklistItemNode.type.name !== 'checklistItem')
                        return false;
                    return commands.updateNode('checklistItem', {
                        checked: !checklistItemNode.attrs.checked,
                    });
                },
            deleteChecklistItem:
                () =>
                ({ commands, state }) => {
                    const { selection } = state;
                    const { $from } = selection;
                    const checklistItemNode = $from.node($from.depth);
                    if (!checklistItemNode || checklistItemNode.type.name !== 'checklistItem')
                        return false;
                    const parent = $from.node($from.depth - 1);
                    if (parent && parent.type.name === 'checklist' && parent.childCount === 1) {
                        return false;
                    }
                    const from = $from.before($from.depth);
                    const to = $from.after($from.depth);
                    return commands.deleteRange({ from, to });
                },
        };
    },

    addNodeView() {
        return ({ node, editor, getPos }) => {
            const isChecked = node.attrs.checked;
            const wrapper = document.createElement('div');
            wrapper.setAttribute('data-type', 'checklist-item');
            wrapper.setAttribute('data-checked', isChecked ? 'true' : 'false');
            wrapper.setAttribute('data-has-focus', 'false');
            wrapper.className = `checklist-item ${isChecked ? 'checked' : ''}`;
            wrapper.style.cssText =
                'display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem; border-radius: 0.5rem; transition: background-color 0.2s ease; position: relative;';

            const inner = document.createElement('div');
            inner.className = 'checklist-item-inner';
            inner.style.cssText =
                'display: flex; align-items: center; gap: 0.75rem; width: 100%; position: relative;';

            // Checkbox
            const checkbox = document.createElement('div');
            checkbox.className = 'checklist-checkbox';
            checkbox.setAttribute('data-action', 'toggle-check');
            checkbox.style.cssText = `flex-shrink: 0; width: 1.25rem; height: 1.25rem; border: 2px solid ${isChecked ? '#6366f1' : '#d1d5db'}; border-radius: 0.375rem; background: ${isChecked ? 'linear-gradient(to right, #6366f1, #8b5cf6)' : 'white'}; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); overflow: hidden;`;

            // Content
            const contentContainer = document.createElement('div');
            contentContainer.className = 'checklist-item-content';
            contentContainer.contentEditable = true;
            contentContainer.style.cssText =
                'display: inline-block; max-width: 100%; outline: none; cursor: text; transition: color 0.3s ease; caret-color: auto; position: relative;';

            // Функция обновления состояния
            const setCheckedState = (checked) => {
                const existingSvg = checkbox.querySelector('svg');
                if (checked) {
                    if (!existingSvg) {
                        const checkIcon = document.createElementNS(
                            'http://www.w3.org/2000/svg',
                            'svg',
                        );
                        checkIcon.setAttribute('width', '14');
                        checkIcon.setAttribute('height', '14');
                        checkIcon.setAttribute('viewBox', '0 0 24 24');
                        checkIcon.setAttribute('fill', 'none');
                        checkIcon.setAttribute('stroke', 'white');
                        checkIcon.setAttribute('stroke-width', '3');
                        checkIcon.classList.add('checkmark-svg');
                        const polyline = document.createElementNS(
                            'http://www.w3.org/2000/svg',
                            'polyline',
                        );
                        polyline.setAttribute('points', '20 6 9 17 4 12');
                        checkIcon.appendChild(polyline);
                        checkbox.appendChild(checkIcon);
                    }
                    checkbox.style.borderColor = '#6366f1';
                    checkbox.style.background = 'linear-gradient(to right, #6366f1, #8b5cf6)';
                } else {
                    if (existingSvg) existingSvg.remove();
                    checkbox.style.borderColor = '#d1d5db';
                    checkbox.style.background = 'white';
                }

                if (checked) {
                    wrapper.classList.add('checked');
                    wrapper.setAttribute('data-checked', 'true');
                } else {
                    wrapper.classList.remove('checked');
                    wrapper.setAttribute('data-checked', 'false');
                }
            };

            setCheckedState(isChecked);

            // Delete button
            const deleteBtn = document.createElement('button');
            deleteBtn.type = 'button';
            deleteBtn.className = 'checklist-delete-btn';
            deleteBtn.setAttribute('data-action', 'delete-item');
            deleteBtn.title = 'Удалить задачу';
            deleteBtn.style.cssText =
                'flex-shrink: 0; width: 1.75rem; height: 1.75rem; border: none; background: #fee2e2; color: #ef4444; border-radius: 0.375rem; cursor: pointer; display: flex; align-items: center; justify-content: center; margin-left: auto;';
            deleteBtn.setAttribute('data-visible', 'false');
            deleteBtn.innerHTML =
                '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="pointer-events: none;"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>';
            deleteBtn.addEventListener('mousedown', (e) => e.stopPropagation());

            // Функция показа/скрытия кнопки удаления
            const setDeleteButtonVisible = (visible) => {
                deleteBtn.setAttribute('data-visible', visible ? 'true' : 'false');
            };

            // Проверка, находится ли фокус в этом элементе
            const isFocusInThisNode = () => {
                const activeEl = document.activeElement;
                const hasDomFocus =
                    activeEl === contentContainer || contentContainer.contains(activeEl);

                const { state } = editor;
                const { selection } = state;
                const { from } = selection;

                const currentPos = typeof getPos === 'function' ? getPos() : null;
                if (currentPos !== null && currentPos !== undefined) {
                    const nodeStart = currentPos;
                    const nodeEnd = currentPos + node.nodeSize;
                    const isInThisNode = from >= nodeStart && from < nodeEnd;
                    return isInThisNode;
                }

                const { $from } = selection;
                let checklistItemNode = null;
                for (let depth = $from.depth; depth >= 0; depth--) {
                    const nodeAtDepth = $from.node(depth);
                    if (nodeAtDepth.type.name === 'checklistItem') {
                        checklistItemNode = nodeAtDepth;
                        break;
                    }
                }

                const hasTipTapFocus = checklistItemNode === node;
                return hasDomFocus || hasTipTapFocus;
            };

            // Управление фоном и видимостью кнопки удаления
            let wasFocused = true;
            const updateBackground = (hasFocus) => {
                if (hasFocus === wasFocused) return;
                wasFocused = hasFocus;
                wrapper.setAttribute('data-has-focus', hasFocus ? 'true' : 'false');
                if (hasFocus) {
                    wrapper.style.setProperty('background-color', '#f9fafb', 'important');
                    wrapper.classList.add('active');
                } else {
                    wrapper.style.setProperty('background-color', 'transparent', 'important');
                    wrapper.classList.remove('active');
                }
                setDeleteButtonVisible(hasFocus);
            };

            const initialFocus = isFocusInThisNode();
            wasFocused = !initialFocus;
            updateBackground(initialFocus);

            contentContainer.addEventListener(
                'focus',
                () => {
                    updateBackground(true);
                },
                true,
            );
            contentContainer.addEventListener(
                'blur',
                () => {
                    setTimeout(() => {
                        const stillHasFocus = isFocusInThisNode();
                        if (!stillHasFocus) {
                            updateBackground(false);
                        }
                    }, 50);
                },
                true,
            );

            const selectionHandler = ({ editor: ed }) => {
                const hasFocus = isFocusInThisNode();
                updateBackground(hasFocus);
            };
            editor.on('selectionUpdate', selectionHandler);

            // Обработчик клика по чекбоксу
            checkbox.addEventListener('mousedown', (e) => {
                e.preventDefault();
                e.stopPropagation();
                if (typeof getPos === 'function') {
                    const pos = getPos();
                    const nodeAtPos = editor.state.doc.nodeAt(pos);
                    if (!nodeAtPos) return;
                    const currentChecked = nodeAtPos.attrs.checked;
                    const newChecked = !currentChecked;
                    editor.commands.command(({ tr }) => {
                        tr.setNodeMarkup(pos, null, { ...nodeAtPos.attrs, checked: newChecked });
                        return true;
                    });
                    // Устанавливаем фокус на contentContainer текущего элемента
                    contentContainer.focus();
                }
            });

            // Обработчик удаления
            deleteBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                if (typeof getPos !== 'function') return;
                const pos = getPos();
                const { state } = editor;
                let targetNode = state.doc.nodeAt(pos);
                let targetPos = pos;
                if (!targetNode || targetNode.type.name !== 'checklistItem') {
                    state.doc.nodesBetween(pos, pos + 1, (node, nodePos) => {
                        if (node.type.name === 'checklistItem') {
                            targetNode = node;
                            targetPos = nodePos;
                            return false;
                        }
                    });
                }
                if (!targetNode || targetNode.type.name !== 'checklistItem') {
                    return;
                }

                const $pos = state.doc.resolve(targetPos);
                const parent = $pos.node($pos.depth - 1);
                if (parent && parent.type.name === 'checklist' && parent.childCount === 1) {
                    return;
                }
                const from = targetPos;
                const to = targetPos + targetNode.nodeSize;
                editor.commands.deleteRange({ from, to });
            });

            inner.appendChild(checkbox);
            inner.appendChild(contentContainer);
            inner.appendChild(deleteBtn);
            wrapper.appendChild(inner);

            const cleanup = () => {
                editor.off('selectionUpdate', selectionHandler);
            };

            return {
                dom: wrapper,
                contentDOM: contentContainer,
                destroy: cleanup,
                update: (updatedNode) => {
                    const newChecked = updatedNode.attrs.checked;
                    setCheckedState(newChecked);
                    setDeleteButtonVisible(isFocusInThisNode());
                    return true;
                },
            };
        };
    },

    addKeyboardShortcuts() {
        return {
            Enter: ({ editor }) => {
                const { state } = editor;
                const { selection } = state;
                const { $from } = selection;
                const checklistItemNode = $from.node($from.depth);
                if (!checklistItemNode || checklistItemNode.type.name !== 'checklistItem')
                    return false;
                const paragraphNode = $from.node($from.depth + 1);
                if (
                    paragraphNode &&
                    paragraphNode.type.name === 'paragraph' &&
                    paragraphNode.childCount === 0
                )
                    return false;
                const newPos = $from.after($from.depth);
                return editor.commands.insertContentAt(newPos, {
                    type: 'checklistItem',
                    attrs: { checked: false },
                    content: [{ type: 'paragraph', content: [] }],
                });
            },

            Backspace: ({ editor }) => {
                const { state } = editor;
                const { selection } = state;
                const { $from } = selection;
                const checklistItemNode = $from.node($from.depth);
                if (!checklistItemNode || checklistItemNode.type.name !== 'checklistItem')
                    return false;
                const paragraphNode = $from.node($from.depth + 1);
                if (
                    paragraphNode &&
                    paragraphNode.type.name === 'paragraph' &&
                    paragraphNode.childCount === 0 &&
                    $from.parentOffset === 0
                ) {
                    const parent = $from.node($from.depth - 1);
                    if (parent && parent.type.name === 'checklist' && parent.childCount === 1) {
                        return true;
                    }
                    const pos = $from.before($from.depth);
                    return editor.commands.deleteRange({
                        from: pos,
                        to: pos + checklistItemNode.nodeSize,
                    });
                }
                return false;
            },

            Delete: ({ editor }) => {
                const { state } = editor;
                const { selection } = state;
                const { $from, $to } = selection;
                const checklistItemNode = $from.node($from.depth);
                if (!checklistItemNode || checklistItemNode.type.name !== 'checklistItem')
                    return false;
                const paragraphNode = $from.node($from.depth + 1);
                if (!paragraphNode || paragraphNode.type.name !== 'paragraph') return false;
                const isEmpty = paragraphNode.childCount === 0;
                const isAllSelected =
                    $from.parentOffset === 0 && $to.parentOffset === paragraphNode.content.size;
                if (isEmpty || isAllSelected) {
                    const parent = $from.node($from.depth - 1);
                    if (parent && parent.type.name === 'checklist' && parent.childCount === 1) {
                        return true;
                    }
                    const pos = $from.before($from.depth);
                    return editor.commands.deleteRange({
                        from: pos,
                        to: pos + checklistItemNode.nodeSize,
                    });
                }
                return false;
            },
        };
    },
});
