class ChecklistEditor {
    constructor(containerId, options = {}) {
        this.container = document.getElementById(containerId);
        if (!this.container) {
            console.error(`Контейнер #${containerId} не найден`);
            return;
        }

        this.options = {
            placeholder: 'Введите задачу',
            buttonLabel: 'Добавить задачу',
            onUpdate: null,
            ...options,
        };

        this.data = []; // массив задач { id, text, checked }
        this.nextId = 1;
        this.itemElements = new Map(); // Храним ссылки на DOM элементы

        this.init();
        this.loadFromJSON(options.initialData);
    }

    init() {
        this.container.innerHTML = '';
        this.container.classList.add('checklist-editor');

        const checklistsContainer = document.createElement('div');
        checklistsContainer.id = 'checklistsContainer';
        checklistsContainer.setAttribute('aria-live', 'polite');
        checklistsContainer.className = 'checklists-container';

        const addRootBtn = document.createElement('button');
        addRootBtn.id = 'addRootBtn';
        addRootBtn.className = 'btn-add';
        addRootBtn.type = 'button';
        addRootBtn.setAttribute('aria-expanded', 'false');
        addRootBtn.innerHTML = `
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="pointer-events: none;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            <span>${this.options.buttonLabel}</span>
        `;

        this.container.appendChild(checklistsContainer);
        this.container.appendChild(addRootBtn);

        this.checklistsContainer = checklistsContainer;
        this.addRootBtn = addRootBtn;

        this.bindEvents();
    }

    bindEvents() {
        this.addRootBtn.addEventListener('click', () => this.addItem());
        this.addRootBtn.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar') {
                e.preventDefault();
                this.addRootBtn.click();
            }
        });
    }

    addItem(text = '', checked = false) {
        const item = {
            id: this.nextId++,
            text,
            checked,
        };
        this.data.push(item);
        this.renderItem(item);
        this.focusItem(item.id);
        this.triggerUpdate();
        return item.id;
    }

    removeItem(id) {
        const itemEl = this.itemElements.get(id);
        if (itemEl) {
            itemEl.remove();
            this.itemElements.delete(id);
        }
        this.data = this.data.filter((item) => item.id !== id);
        // Если задач не осталось, удаляем контейнер checklist
        if (this.data.length === 0) {
            const checklist = this.checklistsContainer.querySelector('.checklist');
            if (checklist) {
                checklist.remove();
            }
        }
        this.triggerUpdate();
    }

    updateItem(id, updates, skipRender = false) {
        const item = this.data.find((item) => item.id === id);
        if (item) {
            const oldChecked = item.checked;
            Object.assign(item, updates);

            // Обновляем DOM элемент без полной перерисовки
            if (!skipRender) {
                this.updateItemElement(id, item, oldChecked !== item.checked);
            }
            this.triggerUpdate();
        }
    }

    // Обновляет DOM элемент задачи
    updateItemElement(id, item, checkedChanged = false) {
        const itemEl = this.itemElements.get(id);
        if (!itemEl) return;

        const checkbox = itemEl.querySelector('.checkbox');
        const taskText = itemEl.querySelector('.task-text');
        const taskContent = itemEl.querySelector('.task-content');

        // Обновляем состояние чекбокса
        if (checkedChanged) {
            if (item.checked) {
                checkbox.classList.add('checked');
                checkbox.setAttribute('aria-checked', 'true');
            } else {
                checkbox.classList.remove('checked');
                checkbox.setAttribute('aria-checked', 'false');
            }
        }

        // Обновляем текст и состояние текста
        if (taskText && document.activeElement !== taskText) {
            taskText.textContent = item.text;
        }

        // Обновляем атрибут data-is-empty
        if (taskText) {
            const isEmpty = !item.text.trim();
            taskText.setAttribute('data-is-empty', isEmpty ? 'true' : 'false');
        }

        // Добавляем класс checked для анимации цвета
        if (taskText) {
            if (item.checked) {
                taskText.classList.add('checked');
            } else {
                taskText.classList.remove('checked');
            }
        }

        if (taskContent) {
            if (item.checked) {
                taskContent.classList.add('checked');
            } else {
                taskContent.classList.remove('checked');
            }
        }
    }

    renderItem(item) {
        // Если элемент уже существует, просто обновляем его
        if (this.itemElements.has(item.id)) {
            this.updateItemElement(item.id, item, true);
            return;
        }

        const itemEl = this.createItemElement(item);

        // Находим .items внутри первого .checklist или создаём новую структуру
        let itemsContainer = this.checklistsContainer.querySelector('.checklist .items');
        if (!itemsContainer) {
            // Создаём новую структуру checklist
            const checklist = document.createElement('section');
            checklist.className = 'checklist';
            itemsContainer = document.createElement('div');
            itemsContainer.className = 'items';
            checklist.appendChild(itemsContainer);
            this.checklistsContainer.appendChild(checklist);
        }

        itemsContainer.appendChild(itemEl);
        this.itemElements.set(item.id, itemEl);
    }

    render() {
        // Полная перерисовка только при первой загрузке
        this.checklistsContainer.innerHTML = '';
        this.itemElements.clear();

        if (this.data.length === 0) {
            // Не показываем пустой чеклист
            return;
        }

        const checklist = document.createElement('section');
        checklist.className = 'checklist';
        const items = document.createElement('div');
        items.className = 'items';

        this.data.forEach((item) => {
            const itemEl = this.createItemElement(item);
            items.appendChild(itemEl);
            this.itemElements.set(item.id, itemEl);
        });

        checklist.appendChild(items);
        this.checklistsContainer.appendChild(checklist);
    }

    createItemElement(item) {
        const itemEl = document.createElement('div');
        itemEl.className = 'checklist-item';
        itemEl.dataset.id = item.id;

        const checkbox = document.createElement('div');
        checkbox.className = `checkbox ${item.checked ? 'checked' : ''}`;
        checkbox.setAttribute('role', 'checkbox');
        checkbox.setAttribute('aria-checked', item.checked ? 'true' : 'false');
        checkbox.tabIndex = 0;
        checkbox.title = 'Отметить задачу';
        checkbox.innerHTML =
            '<svg class="tick" viewBox="0 0 14 11" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><polyline points="1.5 6.2 5.5 9.5 12.5 1.2" fill="none" stroke-linecap="round" stroke-linejoin="round"></polyline></svg>';

        const content = document.createElement('div');
        content.className = 'task-content';
        if (item.checked) {
            content.classList.add('checked');
        }

        const taskText = document.createElement('span');
        taskText.className = 'task-text';
        if (item.checked) {
            taskText.classList.add('checked');
        }
        taskText.contentEditable = true;
        taskText.spellcheck = false;
        taskText.setAttribute('role', 'textbox');
        taskText.setAttribute('aria-multiline', 'true');
        taskText.setAttribute('data-placeholder', this.options.placeholder);
        taskText.textContent = item.text;
        // Устанавливаем атрибут data-is-empty
        const isEmpty = !item.text.trim();
        taskText.setAttribute('data-is-empty', isEmpty ? 'true' : 'false');

        content.appendChild(taskText);

        const btnTrash = document.createElement('button');
        btnTrash.className = 'btn-trash';
        btnTrash.type = 'button';
        btnTrash.title = 'Удалить задачу';
        btnTrash.innerHTML =
            '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M9 3h6"/><path d="M10 3v1a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1V3"/><rect x="6.5" y="6" width="11" height="13" rx="2"></rect><path d="M10 11v5"/><path d="M14 11v5"/></svg>';

        itemEl.appendChild(checkbox);
        itemEl.appendChild(content);
        itemEl.appendChild(btnTrash);

        // Обработчики событий
        checkbox.addEventListener('click', (e) => {
            e.preventDefault();
            this.toggleCheck(item.id, taskText);
        });

        checkbox.addEventListener('keydown', (e) => {
            if (e.key === ' ' || e.key === 'Spacebar' || e.key === 'Enter') {
                e.preventDefault();
                this.toggleCheck(item.id, taskText);
            }
        });

        // Клик по task-content фокусирует taskText и ставит курсор в конец, если клик не по самому тексту
        content.addEventListener('click', (e) => {
            // Если клик по самому taskText или его дочерним элементам, не меняем позицию курсора
            if (e.target === taskText || taskText.contains(e.target)) {
                return;
            }
            taskText.focus();
            // Установить курсор в конец после фокуса
            setTimeout(() => {
                const range = document.createRange();
                const sel = window.getSelection();
                range.selectNodeContents(taskText);
                range.collapse(false);
                sel.removeAllRanges();
                sel.addRange(range);
            }, 0);
        });

        const itemId = item.id;
        let wasEmpty = !item.text.trim();

        taskText.addEventListener('input', () => {
            // Обновляем данные без перерисовки
            const dataItem = this.data.find((i) => i.id === itemId);
            if (dataItem) {
                const textContent = taskText.textContent;
                dataItem.text = textContent;

                // Проверяем пустоту и обновляем атрибут data-is-empty
                const isEmpty = !textContent.trim();
                taskText.setAttribute('data-is-empty', isEmpty ? 'true' : 'false');

                // Если текст пустой и задача отмечена, снимаем галочку
                if (isEmpty && dataItem.checked) {
                    dataItem.checked = false;
                    this.updateItemElement(itemId, dataItem, true);
                }

                // Если текст стал пустым (появился placeholder), ставим курсор в начало
                if (isEmpty && !wasEmpty) {
                    // Очищаем элемент от лишних узлов и ставим курсор в начало
                    taskText.textContent = '';
                    const range = document.createRange();
                    const sel = window.getSelection();
                    range.setStart(taskText, 0);
                    range.collapse(true);
                    sel.removeAllRanges();
                    sel.addRange(range);
                }
                wasEmpty = isEmpty;

                // Проверяем пустоту и убираем класс error если текст появился
                const itemEl = this.itemElements.get(itemId);
                if (itemEl) {
                    const checkbox = itemEl.querySelector('.checkbox');
                    if (taskText.textContent.trim()) {
                        taskText.classList.remove('error');
                        if (checkbox) checkbox.classList.remove('error');
                    }
                }

                this.triggerUpdate();
            }
        });

        taskText.addEventListener('paste', (e) => {
            e.preventDefault();
            const plain = (e.clipboardData || window.clipboardData).getData('text/plain');
            document.execCommand('insertText', false, plain);
            const dataItem = this.data.find((i) => i.id === itemId);
            if (dataItem) {
                dataItem.text = taskText.textContent;
                this.triggerUpdate();
            }
        });

        taskText.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.execCommand('insertText', false, '\n');
            }
        });

        btnTrash.addEventListener('click', (e) => {
            e.stopPropagation();
            this.removeItem(itemId);
        });

        return itemEl;
    }

    toggleCheck(id, taskTextEl) {
        const item = this.data.find((item) => item.id === id);
        if (!item) return;

        // Проверка на пустоту
        if (!item.text.trim()) {
            this.showError(taskTextEl);
            return;
        }

        item.checked = !item.checked;
        // Обновляем DOM элемент с анимацией
        this.updateItemElement(id, item, true);
        this.triggerUpdate();
    }

    showError(taskTextEl) {
        // Находим чекбокс
        const itemEl = taskTextEl.closest('.checklist-item');
        const checkbox = itemEl ? itemEl.querySelector('.checkbox') : null;

        // Добавляем класс error к тексту и чекбоксу
        taskTextEl.classList.add('error');
        if (checkbox) checkbox.classList.add('error');

        // Убираем через 2 секунды
        setTimeout(() => {
            taskTextEl.classList.remove('error');
            if (checkbox) checkbox.classList.remove('error');
        }, 2000);
    }

    focusItem(id) {
        const itemEl = this.itemElements.get(id);
        if (itemEl) {
            const taskText = itemEl.querySelector('.task-text');
            if (taskText) {
                taskText.focus();
                // Поместить курсор в конец
                const range = document.createRange();
                const sel = window.getSelection();
                range.selectNodeContents(taskText);
                range.collapse(false);
                sel.removeAllRanges();
                sel.addRange(range);
            }
        }
    }

    triggerUpdate() {
        if (typeof this.options.onUpdate === 'function') {
            this.options.onUpdate(this.toJSON());
        }
    }

    toJSON() {
        return {
            type: 'doc',
            content: [
                {
                    type: 'checklist',
                    content: this.data.map((item) => ({
                        type: 'checklistItem',
                        attrs: {
                            checked: item.checked,
                        },
                        content: [
                            {
                                type: 'paragraph',
                                content: item.text ? [{ type: 'text', text: item.text }] : [],
                            },
                        ],
                    })),
                },
            ],
        };
    }

    toJSONSimple() {
        // Возвращаем простой массив для внутреннего использования
        return this.data.map((item) => ({
            text: item.text,
            checked: item.checked,
        }));
    }

    extractTextFromContent(content) {
        if (!content || !Array.isArray(content)) return '';

        return content
            .map((node) => {
                if (node.type === 'text') {
                    return node.text || '';
                }
                if (node.type === 'paragraph' && node.content) {
                    return this.extractTextFromContent(node.content);
                }
                return '';
            })
            .join('');
    }

    loadFromJSON(data) {
        if (!data) return;

        if (Array.isArray(data)) {
            // Простой массив задач
            this.data = data.map((item) => ({
                id: this.nextId++,
                text: item.text || '',
                checked: item.checked || false,
            }));
        } else if (data.type === 'doc' && data.content) {
            // Формат TipTap
            this.data = [];
            data.content.forEach((node) => {
                if (node.type === 'checklist' && node.content) {
                    node.content.forEach((itemNode) => {
                        if (itemNode.type === 'checklistItem') {
                            const text = this.extractTextFromContent(itemNode.content);
                            this.data.push({
                                id: this.nextId++,
                                text,
                                checked: itemNode.attrs?.checked || false,
                            });
                        }
                    });
                }
            });
        }

        this.render();
    }

    getData() {
        return this.data;
    }

    destroy() {
        if (this.container) {
            this.container.innerHTML = '';
            this.container.className = '';
        }
        this.data = [];
        this.nextId = 1;
        this.itemElements.clear();
    }
}

export { ChecklistEditor };
