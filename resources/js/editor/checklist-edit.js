import { ChecklistEditor } from './checklist';
import { ChecklistProgressBar } from './checklist-progress';

/**
 * Модуль управления редактором редактирования чек-листа
 */
export default class EditChecklistEditor {
    constructor() {
        this.initialized = false;
        this.observer = null;
        this.editorInstance = null;
        this.progressBarInstance = null;
        this.lastContent = null;
    }

    /**
     * Инициализация модуля
     */
    init() {
        if (this.initialized) return;

        this.setupObserver();
        this.autoInit();
        this.setupLivewireListeners();
        this.initialized = true;
    }

    /**
     * Настройка MutationObserver для отслеживания появления/удаления редактора
     */
    setupObserver() {
        if (this.observer) return;

        this.observer = new MutationObserver((mutations) => {
            for (const mutation of mutations) {
                // Обработка удаленных узлов
                for (const node of mutation.removedNodes) {
                    if (node.nodeType === 1) {
                        if (
                            node.id === 'edit-checklist-editor' ||
                            node.querySelector?.('#edit-checklist-editor')
                        ) {
                            this.destroy();
                        }
                    }
                }

                // Обработка добавленных узлов
                for (const node of mutation.addedNodes) {
                    if (node.nodeType === 1) {
                        if (node.id === 'edit-checklist-editor') {
                            setTimeout(() => this.autoInit(), 50);
                            return;
                        }
                        if (node.querySelector?.('#edit-checklist-editor')) {
                            setTimeout(() => this.autoInit(), 50);
                            return;
                        }
                    }
                }
            }
        });

        this.observer.observe(document.body, {
            childList: true,
            subtree: true,
        });
    }

    /**
     * Автоматическая инициализация редактора
     */
    autoInit() {
        const container = document.getElementById('edit-checklist-editor');
        if (container && !this.editorInstance) {
            this.initEditor();
        }
    }

    /**
     * Инициализация редактора
     */
    initEditor(initialData = null) {
        const container = document.getElementById('edit-checklist-editor');
        if (!container) {
            return null;
        }

        // Уничтожаем существующий редактор и прогресс-бар
        if (this.editorInstance) {
            this.editorInstance.destroy();
            this.editorInstance = null;
        }
        if (this.progressBarInstance) {
            this.progressBarInstance.destroy();
            this.progressBarInstance = null;
        }

        // Очищаем последние данные перед инициализацией
        this.lastContent = null;

        this.editorInstance = new ChecklistEditor('edit-checklist-editor', {
            placeholder: 'Введите задачу',
            buttonLabel: 'Добавить задачу',
            initialData: initialData,
            onUpdate: (json) => {
                this.updateTaskCount(this.editorInstance.getData().length);
                // Сохраняем последние данные
                this.lastContent = json;
                // Обновляем прогресс-бар
                if (this.progressBarInstance) {
                    this.progressBarInstance.update();
                }
                // Обновляем скрытый input для синхронизации с Livewire
                const contentInput = document.getElementById('checklist-content-input');
                if (contentInput) {
                    contentInput.value = JSON.stringify(json);
                    // Триггерим событие input для Livewire
                    contentInput.dispatchEvent(new window.Event('input', { bubbles: true }));
                }
            },
        });

        // Инициализируем прогресс-бар
        this.progressBarInstance = new ChecklistProgressBar(
            this.editorInstance,
            'checklist-progress-bar',
        );

        // Сохраняем начальные данные
        this.lastContent = this.editorInstance.toJSON();

        this.updateTaskCount(this.editorInstance.getData().length);

        return this.editorInstance;
    }

    /**
     * Обновление счетчика задач
     */
    updateTaskCount(count) {
        const taskCountEl = document.querySelector('[data-task-count]');
        if (taskCountEl) {
            const forms = count === 1 ? 'а' : count >= 2 && count <= 4 ? 'и' : '';
            taskCountEl.textContent = `${count} задач${forms}`;
        }
    }

    /**
     * Получение контента редактора
     */
    getContent() {
        return this.lastContent || (this.editorInstance ? this.editorInstance.toJSON() : null);
    }

    /**
     * Переинициализация модуля (вызывается при обновлении состояния)
     */
    reinit() {
        // Если редактор существует, сохраняем его данные
        const currentData = this.editorInstance ? this.editorInstance.toJSON() : null;

        // Уничтожаем текущий редактор
        this.destroy();

        // Переинициализируем с сохраненными данными
        this.autoInit();

        // Если были данные, восстанавливаем их
        if (currentData && this.editorInstance) {
            this.editorInstance.loadFromJSON(currentData);
            this.lastContent = currentData;
        }
    }

    /**
     * Уничтожение редактора
     */
    destroy() {
        if (this.editorInstance) {
            this.editorInstance.destroy();
            this.editorInstance = null;
        }
        if (this.progressBarInstance) {
            this.progressBarInstance.destroy();
            this.progressBarInstance = null;
        }
        this.lastContent = null;
    }

    /**
     * Настройка слушателей событий Livewire
     */
    setupLivewireListeners() {
        // Загрузка данных при редактировании
        document.addEventListener('checklistLoaded', (e) => {
            let parsedContent = e.detail?.content || e.detail;
            if (typeof parsedContent === 'string') {
                try {
                    parsedContent = JSON.parse(parsedContent);
                } catch {
                    parsedContent = '';
                }
            }

            if (this.editorInstance) {
                this.editorInstance.loadFromJSON(parsedContent);
            } else {
                this.initEditor(parsedContent);
            }
        });

        // Обработка событий update-safe-id и update-archive-id
        document.addEventListener('update-safe-id', (e) => {
            if (typeof Livewire !== 'undefined') {
                Livewire.dispatch('updateSafeId', {
                    id: e.detail.id,
                });
            }
        });

        document.addEventListener('update-archive-id', (e) => {
            if (typeof Livewire !== 'undefined') {
                Livewire.dispatch('updateArchiveId', {
                    id: e.detail.id,
                });
            }
        });
    }
}
