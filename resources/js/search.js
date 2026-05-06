/**
 * Модуль для управления поиском
 */
export default class Search {
    constructor() {
        this.initialized = false;
        this.keydownHandler = null;
        this.containerObserver = null;
    }

    /**
     * Инициализация модуля
     */
    init() {
        if (this.initialized) return;

        this.initSearchContainers();
        this.setupContainerObserver();
        this.initialized = true;
    }

    /**
     * Проверка, сфокусирован ли какой-то input
     */
    isInputFocused() {
        const active = document.activeElement;
        if (!active) return false;
        return (
            active.tagName === 'INPUT' || active.tagName === 'TEXTAREA' || active.isContentEditable
        );
    }

    /**
     * Инициализация всех контейнеров поиска
     */
    initSearchContainers() {
        document.querySelectorAll('[data-search-container]').forEach((container) => {
            this.initSearchContainer(container);
        });
    }

    /**
     * Инициализация отдельного контейнера поиска
     */
    initSearchContainer(container) {
        const input = container.querySelector('[data-search-input]');
        const clearButton = container.querySelector('[data-search-clear]');

        if (!input || !clearButton) return;

        // Обновление видимости кнопки очистки
        const updateClearButton = () => {
            if (input.value.trim().length > 0) {
                clearButton.classList.remove('hidden');
            } else {
                clearButton.classList.add('hidden');
            }
        };

        // Начальное состояние
        updateClearButton();

        // Слушаем события input
        input.addEventListener('input', updateClearButton);
        input.addEventListener('change', updateClearButton);

        // Клик по кнопке очистки
        clearButton.addEventListener('click', () => {
            input.value = '';
            input.dispatchEvent(new window.Event('input', { bubbles: true }));
            input.dispatchEvent(new window.Event('change', { bubbles: true }));
            updateClearButton();
            input.focus();
        });

        // MutationObserver для отслеживания изменений значения
        const observer = new MutationObserver(() => {
            updateClearButton();
        });
        observer.observe(input, { attributes: true, attributeFilter: ['value'] });

        // Сохраняем observer для возможной очистки
        container._searchObserver = observer;
    }

    /**
     * Настройка наблюдателя за добавлением новых контейнеров поиска
     */
    setupContainerObserver() {
        if (this.containerObserver) return;

        this.containerObserver = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) {
                        if (node.matches('[data-search-container]')) {
                            this.initSearchContainer(node);
                        }
                        // Проверяем вложенные контейнеры
                        node.querySelectorAll('[data-search-container]').forEach((container) => {
                            this.initSearchContainer(container);
                        });
                    }
                });
            });
        });

        this.containerObserver.observe(document.body, {
            childList: true,
            subtree: true,
        });
    }
}
