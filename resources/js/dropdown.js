class Dropdown {
    constructor(container) {
        this.container = container;
        this.trigger = container.querySelector('.custom-select-trigger');
        this.label = container.querySelector('.custom-select-label');
        this.dropdown = container.querySelector('.custom-select-dropdown');
        this.items = container.querySelectorAll('.custom-select-item');
        this.value = null;
        const dropdownContainer = container.closest('[data-dropdown-container]');
        this.hiddenInput = dropdownContainer
            ? dropdownContainer.querySelector('[data-dropdown-input]')
            : null;

        this.init();
    }

    init() {
        // Открытие/закрытие по клику на триггер
        this.trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggle();
        });

        // Выбор элемента
        this.items.forEach((item) => {
            item.addEventListener('click', (e) => {
                e.stopPropagation();
                this.select(item);
            });
        });

        // Предотвращаем закрытие dropdown при клике внутри него
        this.dropdown.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        // Закрытие при клике вне dropdown
        document.addEventListener('click', (e) => {
            // Если dropdown открыт и клик вне контейнера, закрываем
            if (
                window.getComputedStyle(this.dropdown).display === 'block' &&
                !this.container.contains(e.target)
            ) {
                this.close();
            }
        });

        // Установка изначально выбранного элемента
        const selectedItem = Array.from(this.items).find((item) =>
            item.classList.contains('selected'),
        );
        if (selectedItem) {
            this.value = selectedItem.dataset.value;
            this.label.textContent = selectedItem.textContent;
        }

        // Обновляем скрытый input, если он существует
        if (this.hiddenInput && this.value) {
            this.hiddenInput.value = this.value;
        }
    }

    toggle() {
        const display = window.getComputedStyle(this.dropdown).display;
        if (display === 'block') {
            this.close();
        } else {
            this.open();
        }
    }

    open() {
        this.dropdown.style.display = 'block';
    }

    close() {
        this.dropdown.style.display = 'none';
    }

    select(item) {
        // Удаляем класс selected у всех элементов
        this.items.forEach((i) => i.classList.remove('selected'));
        // Добавляем выбранному элементу
        item.classList.add('selected');

        // Обновляем текст метки
        this.label.textContent = item.textContent;

        // Сохраняем значение
        this.value = item.dataset.value;

        // Закрываем dropdown
        this.close();

        // Обновляем скрытый input
        if (this.hiddenInput) {
            this.hiddenInput.value = this.value;
            // Генерируем событие input для Livewire
            this.hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));

            // Livewire автоматически обновит свойство через событие input
            // (скрытый input имеет wire:model.live)
        }

        // Генерируем пользовательское событие
        const isSafe = item.dataset.safe === 'true';
        this.container.dispatchEvent(
            new CustomEvent('dropdown-change', {
                detail: {
                    value: this.value,
                    text: item.textContent,
                    element: this.container,
                    isSafe: isSafe,
                },
            }),
        );

        // Если выбран safe, генерируем событие для Livewire
        if (isSafe) {
            const event = new CustomEvent('update-safe-id', {
                detail: { id: this.value },
            });
            document.dispatchEvent(event);
        }
    }

    getValue() {
        return this.value;
    }

    setValue(value) {
        const item = Array.from(this.items).find((i) => i.dataset.value === value);
        if (item) {
            this.select(item);
        }
    }
}

// Помечаем инициализированные dropdown, чтобы избежать повторной инициализации
function initDropdowns() {
    const dropdownContainers = document.querySelectorAll(
        '.custom-select:not([data-dropdown-initialized])',
    );

    dropdownContainers.forEach((container) => {
        container.setAttribute('data-dropdown-initialized', 'true');
        new Dropdown(container);
    });
}

// MutationObserver для динамически добавленных dropdown
let dropdownObserver = null;

function setupDropdownObserver() {
    if (dropdownObserver) return;

    dropdownObserver = new MutationObserver((mutationsList) => {
        let hasNewDropdowns = false;

        for (const mutation of mutationsList) {
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType === 1) {
                    if (node.matches('.custom-select') || node.querySelector('.custom-select')) {
                        hasNewDropdowns = true;
                    }
                }
            });
        }

        if (hasNewDropdowns) {
            // Повторно инициализируем dropdown
            initDropdowns();
        }
    });

    dropdownObserver.observe(document.body, {
        childList: true,
        subtree: true,
    });
}

// Основная инициализация
function initialize() {
    initDropdowns();
    setupDropdownObserver();
}

// Инициализация при готовности DOM
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialize);
} else {
    initialize();
}

// Повторная инициализация после обновлений Livewire
if (typeof Livewire !== 'undefined') {
    Livewire.hook('element.updated', (el) => {
        // Проверяем, содержит ли обновлённый элемент dropdown
        const dropdownsInside = el.querySelectorAll(
            '.custom-select:not([data-dropdown-initialized])',
        );
        if (dropdownsInside.length) {
            setTimeout(initDropdowns, 10);
        }
    });
    Livewire.hook('message.processed', () => {
        // Небольшая задержка для гарантии полного обновления DOM
        setTimeout(initDropdowns, 10);
    });
}
