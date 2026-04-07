import { ChecklistEditor } from './checklist-editor';
import { initChecklistProgressBar } from './checklist-progress';

// Приватное состояние модуля (замыкание)
let editorInstance = null;
let progressBarInstance = null;
let lastContent = null;

function updateTaskCount(count) {
    const taskCountEl = document.querySelector('[data-task-count]');
    if (taskCountEl) {
        const forms = count === 1 ? 'а' : count >= 2 && count <= 4 ? 'и' : '';
        taskCountEl.textContent = `${count} задач${forms}`;
    }
}

function initCreateChecklistEditor(initialData = null) {
    const container = document.getElementById('create-checklist-editor');
    if (!container) {
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

    editorInstance = new ChecklistEditor('create-checklist-editor', {
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
            // Обновляем скрытый input для синхронизации с Livewire
            const contentInput = document.getElementById('checklist-content-input');
            if (contentInput) {
                contentInput.value = JSON.stringify(json);
                // Триггерим событие input для Livewire
                contentInput.dispatchEvent(new Event('input', { bubbles: true }));
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

// Получает данные из редактора создания (приватная функция)
function getCreateEditorContent() {
    return lastContent || (editorInstance ? editorInstance.toJSON() : null);
}

function destroyCreateEditor() {
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

// Автоматическая инициализация редактора создания
function autoInitCreateEditor() {
    const container = document.getElementById('create-checklist-editor');
    if (container) {
        if (!editorInstance) {
            initCreateChecklistEditor(null);
        }
    }
}

// MutationObserver для отслеживания появления и удаления элементов редактора
const checklistObserver = new MutationObserver((mutations) => {
    for (const mutation of mutations) {
        for (const node of mutation.removedNodes) {
            if (node.nodeType === 1) {
                if (
                    node.id === 'create-checklist-editor' ||
                    node.querySelector?.('#create-checklist-editor')
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
                if (node.id === 'create-checklist-editor') {
                    setTimeout(autoInitCreateEditor, 50);
                    return;
                }
                if (node.querySelector?.('#create-checklist-editor')) {
                    setTimeout(autoInitCreateEditor, 50);
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
        autoInitCreateEditor();
    });
} else {
    checklistObserver.observe(document.body, { childList: true, subtree: true });
    autoInitCreateEditor();
}

// Обработка событий Livewire
if (typeof Livewire !== 'undefined') {
    Livewire.hook('component.init', () => {
        setTimeout(autoInitCreateEditor, 50);
    });

    // Слушаем запрос на получение контента от PHP (только если редактор существует)
    Livewire.on('getChecklistContent', () => {
        const container = document.getElementById('create-checklist-editor');
        if (container && editorInstance) {
            // Отправляем контент через событие (для обратной совместимости)
            const content = getCreateEditorContent();
            Livewire.dispatch('checklistContentReady', { content: JSON.stringify(content) });
        }
    });

    // Очистка после сохранения
    Livewire.on('checklistSaved', () => {
        lastContent = null;
    });
}

export { destroyCreateEditor, getCreateEditorContent, initCreateChecklistEditor };
