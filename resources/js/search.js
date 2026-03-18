export function initSearch() {
    // Фокус на поле поиска при нажатии клавиши '/'
    document.addEventListener('keydown', (e) => {
        if (e.key === '/' && !isInputFocused()) {
            e.preventDefault();
            const searchInput = document.querySelector('[data-search-input]');
            if (searchInput) {
                searchInput.focus();
            }
        }
    });

    // Инициализация всех компонентов поиска
    const initSearchContainer = (container) => {
        const input = container.querySelector('[data-search-input]');
        const clearButton = container.querySelector('[data-search-clear]');

        if (!input || !clearButton) return;

        // Обновление видимости кнопки очистки в зависимости от значения поля
        const updateClearButton = () => {
            if (input.value.trim().length > 0) {
                clearButton.classList.remove('hidden');
            } else {
                clearButton.classList.add('hidden');
            }
        };

        // Начальное состояние
        updateClearButton();

        // Слушаем события input (Livewire также может изменять значение)
        input.addEventListener('input', updateClearButton);
        input.addEventListener('change', updateClearButton);

        // Клик по кнопке очистки
        clearButton.addEventListener('click', () => {
            input.value = '';
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
            updateClearButton();
            input.focus();
        });

        // Livewire может обновлять значение через wire:model, отслеживаем через MutationObserver
        const observer = new MutationObserver(() => {
            updateClearButton();
        });
        observer.observe(input, { attributes: true, attributeFilter: ['value'] });

        // Сохраняем observer для возможной очистки (необязательно)
        container._searchObserver = observer;
    };

    // Инициализация существующих контейнеров
    document.querySelectorAll('[data-search-container]').forEach(initSearchContainer);

    // Наблюдение за добавлением новых контейнеров (например, после навигации Livewire)
    const containerObserver = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType === 1) {
                    if (node.matches('[data-search-container]')) {
                        initSearchContainer(node);
                    }
                    // Также проверяем вложенные контейнеры
                    node.querySelectorAll('[data-search-container]').forEach(initSearchContainer);
                }
            });
        });
    });

    containerObserver.observe(document.body, {
        childList: true,
        subtree: true,
    });

    // Также переинициализируем при событиях Livewire
    if (window.Livewire) {
        window.Livewire.hook('commit', ({ succeed }) => {
            succeed(() => {
                // После успешного обновления компонента инициализируем новые контейнеры
                setTimeout(() => {
                    document.querySelectorAll('[data-search-container]').forEach((container) => {
                        if (!container._searchInitialized) {
                            initSearchContainer(container);
                            container._searchInitialized = true;
                        }
                    });
                }, 10);
            });
        });
    }
}

function isInputFocused() {
    const active = document.activeElement;
    if (!active) return false;
    return active.tagName === 'INPUT' || active.tagName === 'TEXTAREA' || active.isContentEditable;
}

// Автоматическая инициализация при импорте через бандл
if (typeof window !== 'undefined') {
    window.addEventListener('DOMContentLoaded', () => {
        initSearch();
    });
}
