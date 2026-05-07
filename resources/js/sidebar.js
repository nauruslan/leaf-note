/**
 * Управление боковой панелью навигации
 * Обрабатывает наведение, сворачивание/разворачивание, сохранение позиции прокрутки
 * и интеграцию с Livewire
 */
export default class Sidebar {
    constructor() {
        this.initialized = false;
        this.sidebar = null;
        this.nav = null;
        this.observer = null;

        // Состояние компонента
        this.isHovered = false;
        this.isNavigating = false;
        this.collapseTimer = null;
        this.scrollTimeout = null;
        this.STORAGE_KEY = 'sidebar_scroll';

        // Привязанные методы для корректного удаления обработчиков
        this.boundHandleMouseEnter = this.handleMouseEnter.bind(this);
        this.boundHandleMouseLeave = this.handleMouseLeave.bind(this);
        this.boundHandleScroll = this.handleScroll.bind(this);
        this.boundHandleBeforeUnload = this.clearAllTimers.bind(this);
        this.boundHandleNavigateTo = this.handleNavigateTo.bind(this);
        this.boundHandleStateUpdated = this.handleStateUpdated.bind(this);
        this.boundHandleClick = this.handleClick.bind(this);
        this.boundHandleLivewireInit = this.handleLivewireInit.bind(this);
        this.boundHandleLivewireUpdated = this.handleLivewireUpdated.bind(this);
    }

    /**
     * Инициализация модуля
     */
    init() {
        if (this.initialized) return;

        this.initSidebar();
        this.setupSidebarObserver();
        this.setupLivewireEvents();
        this.initialized = true;
    }

    /**
     * Инициализация боковой панели
     */
    initSidebar() {
        this.sidebar = document.getElementById('navigation-sidebar');
        this.nav = document.getElementById('sidebar-nav');

        if (!this.sidebar || !this.nav) {
            return;
        }

        this.updateExpandedAttribute();
        this.restoreScrollPosition();
        this.setupEventListeners();

        // Если мышь уже находится над сайдбаром (например, после Livewire-обновления),
        // сразу разворачиваем панель, чтобы не было моргания
        if (this.isHovered) {
            this.expand();
        }

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
        window.addEventListener('stateUpdated', this.boundHandleStateUpdated);

        // Обработчик кликов по ссылкам навигации
        this.sidebar.addEventListener('click', this.boundHandleClick);
    }

    /**
     * Настройка наблюдателя за изменениями DOM
     */
    setupSidebarObserver() {
        if (this.observer) return;

        this.observer = new MutationObserver((mutationsList) => {
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
                setTimeout(() => this.initSidebar(), 10);
            }
        });

        this.observer.observe(document.body, {
            childList: true,
            subtree: true,
        });
    }

    /**
     * Настройка событий Livewire 4
     */
    setupLivewireEvents() {
        // Инициализация при загрузке Livewire
        document.addEventListener('livewire:init', this.boundHandleLivewireInit);

        // Переинициализация при обновлении DOM
        document.addEventListener('livewire:updated', this.boundHandleLivewireUpdated);
    }

    /**
     * Обработчик события livewire:init
     */
    handleLivewireInit() {
        this.initSidebar();
    }

    /**
     * Обработчик события livewire:updated
     */
    handleLivewireUpdated() {
        this.initSidebar();
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

            // Сворачиваем через DOM
            this.collapse();

            // Больше не отправляем событие серверу, состояние управляется на клиенте
        }, 150);
    }

    /**
     * Обработчик кликов по ссылкам навигации
     */
    handleClick(e) {
        const link = e.target.closest('a[wire\\:click]');
        if (link) {
            this.isNavigating = true;
            this.clearAllTimers();
        }
    }

    /**
     * Обработчик события навигации
     */
    handleNavigateTo() {
        // Флаг уже установлен в обработчике клика
        this.clearAllTimers();

        // После навигации сворачиваем через DOM
        setTimeout(() => {
            this.isNavigating = false;
            if (!this.isHovered) {
                this.collapse();
            }
        }, 250);

        // Центрируем активный элемент в скролле
        setTimeout(() => this.scrollToActiveItem(), 300);
    }

    /**
     * Обработчик обновления состояния
     */
    handleStateUpdated() {
        // Центрируем активный элемент в скролле после обновления состояния
        setTimeout(() => this.scrollToActiveItem(), 150);
    }

    /**
     * Обработчик прокрутки навигационного контейнера
     */
    handleScroll() {
        this.clearScrollTimeout();
        this.scrollTimeout = setTimeout(() => {
            if (this.nav) {
                window.localStorage.setItem(this.STORAGE_KEY, this.nav.scrollTop);
            }
        }, 100);
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

        // Сворачиваем заголовки секций
        this.sidebar.querySelectorAll('.sidebar-section-title').forEach((el) => {
            el.classList.remove('py-2', 'max-h-10');
            el.classList.add('py-0', 'max-h-0');
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

        // Разворачиваем заголовки секций
        this.sidebar.querySelectorAll('.sidebar-section-title').forEach((el) => {
            el.classList.remove('py-0', 'max-h-0');
            el.classList.add('py-2', 'max-h-10');
        });
    }

    /**
     * Восстановление сохраненной позиции прокрутки
     */
    restoreScrollPosition() {
        if (!this.nav) {
            return;
        }

        const savedScroll = window.localStorage.getItem(this.STORAGE_KEY);
        if (savedScroll !== null) {
            requestAnimationFrame(() => {
                this.nav.scrollTop = parseInt(savedScroll, 10);
            });
        }
    }

    /**
     * Прокрутка к активному пункту меню
     */
    scrollToActiveItem() {
        if (!this.nav) {
            // Повторяем попытку через 50 мс, если nav еще не доступен
            setTimeout(() => this.scrollToActiveItem(), 50);
            return;
        }

        // Ищем активную ссылку
        const activeLink = this.nav.querySelector('.sidebar-active-item');
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
     * Переинициализация модуля
     */
    reinit() {
        this.destroy();
        this.init();
    }

    /**
     * Уничтожение экземпляра и очистка ресурсов
     */
    destroy() {
        // Удаляем обработчики событий
        if (this.sidebar) {
            this.sidebar.removeEventListener('mouseenter', this.boundHandleMouseEnter);
            this.sidebar.removeEventListener('mouseleave', this.boundHandleMouseLeave);
            this.sidebar.removeEventListener('click', this.boundHandleClick);
        }

        if (this.nav) {
            this.nav.removeEventListener('scroll', this.boundHandleScroll);
        }

        document.removeEventListener('beforeunload', this.boundHandleBeforeUnload);
        window.removeEventListener('navigateTo', this.boundHandleNavigateTo);
        window.removeEventListener('stateUpdated', this.boundHandleStateUpdated);
        document.removeEventListener('livewire:init', this.boundHandleLivewireInit);
        document.removeEventListener('livewire:updated', this.boundHandleLivewireUpdated);

        // Отключаем наблюдатель
        if (this.observer) {
            this.observer.disconnect();
            this.observer = null;
        }

        // Очищаем таймеры
        this.clearAllTimers();

        // Сбрасываем ссылки
        this.sidebar = null;
        this.nav = null;
        this.initialized = false;
    }
}
