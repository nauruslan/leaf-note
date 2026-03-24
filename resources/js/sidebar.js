/**
 * Класс для управления боковой панелью навигации
 * Обрабатывает наведение, сворачивание/разворачивание, сохранение позиции прокрутки
 * и интеграцию с Livewire
 */
class Sidebar {
    constructor(container) {
        this.container = container;
        this.sidebar = document.getElementById('navigation-sidebar');
        this.nav = document.getElementById('sidebar-nav');

        // Состояние компонента
        this.isHovered = false; // Наведена ли мышь на панель
        this.isNavigating = false; // Выполняется ли навигация
        this.collapseTimer = null; // Таймер для сворачивания
        this.scrollTimeout = null; // Таймер для сохранения позиции скролла
        this.STORAGE_KEY = 'sidebar_scroll'; // Ключ для localStorage

        // Привязанные методы для корректного удаления обработчиков
        this.boundHandleMouseEnter = this.handleMouseEnter.bind(this);
        this.boundHandleMouseLeave = this.handleMouseLeave.bind(this);
        this.boundHandleScroll = this.handleScroll.bind(this);
        this.boundHandleBeforeUnload = this.clearAllTimers.bind(this);
        this.boundHandleNavigateTo = this.handleNavigateTo.bind(this);
        this.boundHandleScrollToActiveItem = this.handleScrollToActiveItem.bind(this);
        this.boundHandleMessageSent = this.handleMessageSent.bind(this);
        this.boundHandleMessageProcessed = this.handleMessageProcessed.bind(this);

        this.init();
    }

    /**
     * Инициализация боковой панели
     */
    init() {
        if (!this.sidebar || !this.nav) {
            return;
        }

        this.updateExpandedAttribute();
        this.restoreScrollPosition();
        this.setupEventListeners();
        // Небольшая задержка для корректного позиционирования активного элемента
        setTimeout(() => this.scrollToActiveItem(), 200);
    }

    /**
     * Настройка всех обработчиков событий
     */
    setupEventListeners() {
        // События мыши на боковой панели
        this.sidebar.addEventListener('mouseenter', this.boundHandleMouseEnter);
        this.sidebar.addEventListener('mouseleave', this.boundHandleMouseLeave);

        // Событие прокрутки на навигационном контейнере
        this.nav.addEventListener('scroll', this.boundHandleScroll);

        // Событие перед закрытием страницы
        document.addEventListener('beforeunload', this.boundHandleBeforeUnload);

        // Кастомные события Livewire
        window.addEventListener('navigateTo', this.boundHandleNavigateTo);
        window.addEventListener('scrollToActiveItem', this.boundHandleScrollToActiveItem);

        // Хуки Livewire
        window.Livewire.hook('messageSent', this.boundHandleMessageSent);
        window.Livewire.hook('messageProcessed', this.boundHandleMessageProcessed);
    }

    /**
     * Удаление всех обработчиков событий
     */
    removeEventListeners() {
        if (this.sidebar) {
            this.sidebar.removeEventListener('mouseenter', this.boundHandleMouseEnter);
            this.sidebar.removeEventListener('mouseleave', this.boundHandleMouseLeave);
        }

        if (this.nav) {
            this.nav.removeEventListener('scroll', this.boundHandleScroll);
        }

        document.removeEventListener('beforeunload', this.boundHandleBeforeUnload);
        window.removeEventListener('navigateTo', this.boundHandleNavigateTo);
        window.removeEventListener('scrollToActiveItem', this.boundHandleScrollToActiveItem);

        window.Livewire.hook('messageSent', this.boundHandleMessageSent);
        window.Livewire.hook('messageProcessed', this.boundHandleMessageProcessed);

        this.clearAllTimers();
    }

    /**
     * Обработчик наведения мыши на боковую панель
     */
    handleMouseEnter() {
        this.isHovered = true;
        this.clearAllTimers();
        this.updateExpandedAttribute();

        // Если панель свернута, разворачиваем её
        if (this.sidebar && this.sidebar.classList.contains('w-[72px]')) {
            this.expand();
        }
    }

    /**
     * Обработчик ухода мыши с боковой панели
     */
    handleMouseLeave() {
        this.isHovered = false;
        this.clearAllTimers();
        this.updateExpandedAttribute();

        // Задержка перед сворачиванием, чтобы избежать случайного закрытия
        this.collapseTimer = setTimeout(() => {
            // Не сворачиваем во время навигации
            if (this.isNavigating) {
                return;
            }

            // Проверяем существование элементов в DOM
            if (!this.sidebar || !document.contains(this.sidebar)) {
                return;
            }

            try {
                // Пытаемся сбросить флаг через Livewire
                window.Livewire.dispatch('clearSidebarFlag');
            } catch (e) {
                // Если Livewire недоступен, сворачиваем через DOM
                this.collapse();
            }
        }, 150);
    }

    /**
     * Обработчик события навигации
     * @param {Event} event - Событие навигации
     */
    handleNavigateTo(event) {
        this.isNavigating = true;
        this.clearAllTimers();

        setTimeout(() => {
            this.isNavigating = false;
            // Если мышь не наведена, сворачиваем панель
            if (!this.isHovered) {
                this.collapse();
            }
            // Переинициализация после навигации
            setTimeout(() => {
                this.sidebar = document.getElementById('navigation-sidebar');
                this.nav = document.getElementById('sidebar-nav');
                if (this.sidebar && this.nav) {
                    this.updateExpandedAttribute();
                    this.restoreScrollPosition();
                }
            }, 300);
        }, 250);
    }

    /**
     * Обработчик отправки сообщения Livewire
     * @param {Object} message - Сообщение Livewire
     * @param {Object} component - Компонент Livewire
     */
    handleMessageSent(message, component) {
        // Очищаем таймеры при взаимодействии с компонентом навигации
        if (component.name === 'navigation-sidebar') {
            this.clearAllTimers();
        }
    }

    /**
     * Обработчик завершения обработки сообщения Livewire
     * @param {Object} message - Сообщение Livewire
     * @param {Object} component - Компонент Livewire
     */
    handleMessageProcessed(message, component) {
        // Очищаем таймеры при взаимодействии с компонентом навигации
        if (component.name === 'navigation-sidebar') {
            this.clearAllTimers();
        }
    }

    /**
     * Обработчик прокрутки навигационного контейнера
     * Сохраняет позицию прокрутки в localStorage
     */
    handleScroll() {
        this.clearScrollTimeout();
        this.scrollTimeout = setTimeout(() => {
            if (this.nav) {
                localStorage.setItem(this.STORAGE_KEY, this.nav.scrollTop);
            }
        }, 100);
    }

    /**
     * Обработчик прокрутки к активному элементу
     */
    handleScrollToActiveItem() {
        setTimeout(() => this.scrollToActiveItem(), 500);
    }

    /**
     * Очистка всех таймеров
     */
    clearAllTimers() {
        if (this.collapseTimer) {
            clearTimeout(this.collapseTimer);
            this.collapseTimer = null;
        }
        this.clearScrollTimeout();
    }

    /**
     * Очистка таймера прокрутки
     */
    clearScrollTimeout() {
        if (this.scrollTimeout) {
            clearTimeout(this.scrollTimeout);
            this.scrollTimeout = null;
        }
    }

    /**
     * Обновление атрибута data-expanded в зависимости от состояния
     */
    updateExpandedAttribute() {
        if (this.sidebar) {
            const hasFullWidth = this.sidebar.classList.contains('w-64');
            const shouldShowScrollbar = hasFullWidth || this.isHovered;
            this.sidebar.setAttribute('data-expanded', shouldShowScrollbar ? 'true' : 'false');
        }
    }

    /**
     * Сворачивание боковой панели
     */
    collapse() {
        // Проверяем существование элементов
        if (!this.sidebar || !document.contains(this.sidebar)) {
            return;
        }

        // Изменяем классы ширины
        this.sidebar.classList.remove('w-64');
        this.sidebar.classList.add('w-[72px]');
        this.sidebar.setAttribute('data-expanded', 'false');

        // Отключаем прокрутку навигационного контейнера
        if (this.nav && document.contains(this.nav)) {
            this.nav.classList.remove('overflow-y-auto');
            this.nav.classList.add('overflow-hidden');
        }

        // Скрываем текстовые элементы (делаем их прозрачными)
        this.sidebar.querySelectorAll('.opacity-100').forEach((el) => {
            el.classList.remove('opacity-100');
            el.classList.add('opacity-0');
        });
    }

    /**
     * Разворачивание боковой панели
     */
    expand() {
        // Проверяем существование элементов
        if (!this.sidebar || !document.contains(this.sidebar)) {
            return;
        }

        // Изменяем классы ширины
        this.sidebar.classList.remove('w-[72px]');
        this.sidebar.classList.add('w-64');
        this.sidebar.setAttribute('data-expanded', 'true');

        // Включаем прокрутку навигационного контейнера
        if (this.nav && document.contains(this.nav)) {
            this.nav.classList.remove('overflow-hidden');
            this.nav.classList.add('overflow-y-auto');
        }

        // Показываем текстовые элементы (делаем их видимыми)
        this.sidebar.querySelectorAll('.opacity-0').forEach((el) => {
            el.classList.remove('opacity-0');
            el.classList.add('opacity-100');
        });
    }

    /**
     * Восстановление сохраненной позиции прокрутки
     */
    restoreScrollPosition() {
        if (!this.nav) {
            return;
        }

        const savedScroll = localStorage.getItem(this.STORAGE_KEY);
        if (savedScroll !== null) {
            requestAnimationFrame(() => {
                this.nav.scrollTop = parseInt(savedScroll, 10);
            });
        }
    }

    /**
     * Прокрутка к активному пункту меню
     * Активный пункт определяется по наличию градиентного фона
     */
    scrollToActiveItem() {
        if (!this.nav) {
            // Повторяем попытку через 50 мс, если nav еще не доступен
            setTimeout(() => this.scrollToActiveItem(), 50);
            return;
        }

        // Ищем активную ссылку (с градиентным фоном)
        const activeLink = this.nav.querySelector(
            '.bg-gradient-to-r.from-indigo-600.to-purple-600',
        );
        if (!activeLink) return;

        // Вычисляем позицию для прокрутки
        const navRect = this.nav.getBoundingClientRect();
        const linkRect = activeLink.getBoundingClientRect();

        const scrollTop =
            this.nav.scrollTop +
            linkRect.top -
            navRect.top -
            navRect.height / 2 +
            linkRect.height / 2;

        // Плавная прокрутка к активному элементу
        this.nav.scrollTo({
            top: scrollTop,
            behavior: 'smooth',
        });
    }

    /**
     * Уничтожение экземпляра и очистка ресурсов
     */
    destroy() {
        this.removeEventListeners();
        this.container = null;
        this.sidebar = null;
        this.nav = null;
    }
}

let sidebarInstance = null; // Текущий экземпляр Sidebar
let sidebarObserver = null; // MutationObserver для отслеживания появления панели

/**
 * Инициализация боковой панели
 * Создает новый экземпляр или пересоздает существующий
 */
function initSidebar() {
    const sidebar = document.getElementById('navigation-sidebar');

    if (!sidebar) {
        return;
    }

    // Удаляем существующий экземпляр
    if (sidebarInstance) {
        sidebarInstance.destroy();
        sidebarInstance = null;
    }

    sidebarInstance = new Sidebar(sidebar);
}

/**
 * Настройка наблюдателя за изменениями DOM
 * Отслеживает появление боковой панели и инициализирует её
 */
function setupSidebarObserver() {
    if (sidebarObserver) return;

    sidebarObserver = new MutationObserver((mutationsList) => {
        let hasNewSidebar = false;

        for (const mutation of mutationsList) {
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType === 1) {
                    if (
                        node.id === 'navigation-sidebar' ||
                        node.querySelector('#navigation-sidebar')
                    ) {
                        hasNewSidebar = true;
                    }
                }
            });
        }

        if (hasNewSidebar) {
            // Небольшая задержка для завершения рендеринга DOM
            setTimeout(initSidebar, 10);
        }
    });

    sidebarObserver.observe(document.body, {
        childList: true,
        subtree: true,
    });
}

/**
 * Основная инициализация
 */
function initialize() {
    initSidebar();
    setupSidebarObserver();
}

// Инициализация при загрузке DOM
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialize);
} else {
    initialize();
}

if (typeof Livewire !== 'undefined') {
    // Переинициализация при обновлении элемента
    Livewire.hook('element.updated', (el) => {
        if (el.id === 'navigation-sidebar' || el.querySelector?.('#navigation-sidebar')) {
            setTimeout(initSidebar, 10);
        }
    });

    // Переинициализация после обработки сообщения
    Livewire.hook('message.processed', () => {
        setTimeout(initSidebar, 10);
    });
}

/**
 * Интеграция с Turbo/Turbolinks
 * Обработчик загрузки страницы в Turbo
 */
document.addEventListener('turbo:load', () => {
    if (sidebarInstance) {
        sidebarInstance.destroy();
        sidebarInstance = null;
    }
    setTimeout(initSidebar, 10);
});

/**
 * Обработчик перед кэшированием страницы в Turbo
 */
document.addEventListener('turbo:before-cache', () => {
    if (sidebarInstance) {
        sidebarInstance.destroy();
        sidebarInstance = null;
    }
});

/**
 * Глобальный объект для управления боковой панелью
 * Позволяет вручную вызывать методы Sidebar из консоли или другого кода
 */
window.SidebarManager = {
    init: initSidebar,
    getInstance: () => sidebarInstance,
    collapse: () => sidebarInstance?.collapse(),
    expand: () => sidebarInstance?.expand(),
    clearTimers: () => sidebarInstance?.clearAllTimers(),
    scrollToActive: () => sidebarInstance?.scrollToActiveItem(),
};
