import { ChecklistEditor } from './checklist-editor';
import { initChecklistProgressBar } from './checklist-progress';

// Приватное состояние модуля (замыкание)
let editorInstance = null;
let progressBarInstance = null;
let lastContent = null;

// Обновляет счётчик задач в футере редактора
function updateTaskCount(count) {
    const taskCountEl = document.querySelector('[data-task-count]');
    if (taskCountEl) {
        const forms = count === 1 ? 'а' : count >= 2 && count <= 4 ? 'и' : '';
        taskCountEl.textContent = `${count} задач${forms}`;
    }
}

// Инициализирует редактор для редактирования чеклиста
function initEditChecklistEditor(initialData = null) {
    const container = document.getElementById('edit-checklist-editor');
    if (!container) {
        console.error('[EditChecklistEditor] Element #edit-checklist-editor not found');
        return null;
    }

    // Уничтожаем существующий редактор и прогресс-бар
    if (editorInstance) {
        editorInstance.destroy();
        editorInstance = null;
    }
    if (progressBarInstance) {
        progressBarInstance.destroy();
        progressBarInstance = null;
    }

    // Очищаем последние данные перед инициализацией
    lastContent = null;

    editorInstance = new ChecklistEditor('edit-checklist-editor', {
        placeholder: 'Введите задачу',
        buttonLabel: 'Добавить задачу',
        initialData: initialData,
        onUpdate: (json) => {
            updateTaskCount(editorInstance.getData().length);
            // Сохраняем последние данные в замыкании
            lastContent = json;
            // Обновляем прогресс-бар
            if (progressBarInstance) {
                progressBarInstance.update();
            }
        },
    });

    // Инициализируем прогресс-бар
    progressBarInstance = initChecklistProgressBar(editorInstance, 'checklist-progress-bar');

    // Сохраняем начальные данные
    lastContent = editorInstance.toJSON();

    updateTaskCount(editorInstance.getData().length);

    return editorInstance;
}

// Получает данные из редактора редактирования
function getEditEditorContent() {
    return lastContent || (editorInstance ? editorInstance.toJSON() : null);
}

// Уничтожает экземпляр редактора и прогресс-бар
function destroyEditEditor() {
    if (editorInstance) {
        editorInstance.destroy();
        editorInstance = null;
    }
    if (progressBarInstance) {
        progressBarInstance.destroy();
        progressBarInstance = null;
    }
    lastContent = null;
}

// Отправляет контент в Livewire через событие (только если редактор существует)
function sendContentToLivewire() {
    if (typeof Livewire !== 'undefined' && editorInstance) {
        const content = getEditEditorContent();
        Livewire.dispatch('checklistContentReady', { content: JSON.stringify(content) });
    }
}

// Автоматическая инициализация редактора редактирования
function autoInitEditEditor() {
    const container = document.getElementById('edit-checklist-editor');
    if (container) {
        if (!editorInstance) {
            initEditChecklistEditor(null);
        }
    }
}

// MutationObserver для отслеживания появления и удаления элементов редактора
const checklistObserver = new MutationObserver((mutations) => {
    for (const mutation of mutations) {
        for (const node of mutation.removedNodes) {
            if (node.nodeType === 1) {
                if (
                    node.id === 'edit-checklist-editor' ||
                    node.querySelector?.('#edit-checklist-editor')
                ) {
                    if (editorInstance) {
                        editorInstance.destroy();
                        editorInstance = null;
                    }
                    if (progressBarInstance) {
                        progressBarInstance.destroy();
                        progressBarInstance = null;
                    }
                    lastContent = null;
                }
            }
        }

        for (const node of mutation.addedNodes) {
            if (node.nodeType === 1) {
                if (node.id === 'edit-checklist-editor') {
                    setTimeout(autoInitEditEditor, 50);
                    return;
                }
                if (node.querySelector?.('#edit-checklist-editor')) {
                    setTimeout(autoInitEditEditor, 50);
                    return;
                }
            }
        }
    }
});

// Инициализация при загрузке страницы
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        checklistObserver.observe(document.body, { childList: true, subtree: true });
        autoInitEditEditor();
    });
} else {
    checklistObserver.observe(document.body, { childList: true, subtree: true });
    autoInitEditEditor();
}

// Обработка событий Livewire
if (typeof Livewire !== 'undefined') {
    Livewire.hook('component.init', ({ component }) => {
        setTimeout(() => {
            autoInitEditEditor();
        }, 50);
    });

    // Загрузка данных при редактировании
    Livewire.on('checklistLoaded', (data) => {
        let parsedContent = data?.content || data;
        if (typeof parsedContent === 'string') {
            try {
                parsedContent = JSON.parse(parsedContent);
            } catch (e) {
                parsedContent = '';
            }
        }

        if (editorInstance) {
            editorInstance.loadFromJSON(parsedContent);
        } else {
            initEditChecklistEditor(parsedContent);
        }
    });

    // Слушаем запрос на получение контента от PHP (только если редактор существует)
    Livewire.on('getChecklistContent', () => {
        const container = document.getElementById('edit-checklist-editor');
        if (container && editorInstance) {
            sendContentToLivewire();
        }
    });

    // Очистка после сохранения
    Livewire.on('checklistSaved', () => {
        lastContent = null;
    });

    Livewire.on('checklistUpdated', () => {
        lastContent = null;
    });
}

// Экспортируем только то, что нужно для тестов (опционально)
export { destroyEditEditor, getEditEditorContent, initEditChecklistEditor, sendContentToLivewire };
