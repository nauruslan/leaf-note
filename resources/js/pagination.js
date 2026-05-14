/**
 * Управление пагинацией с контролируемой прокруткой
 */
export default class Pagination {
    constructor() {
        this.initialized = false;
        this.observer = null;
        this.lastActivePage = null;
        this.isNavigating = false;
        this.scrollTimeout = null;
    }

    /**
     * Инициализация модуля
     */
    init() {
        if (this.initialized) return;

        this.setupPageChangeObserver();
        this.setupEventListeners();
        this.initialized = true;
    }

    /**
     * Настройка обработчиков событий
     */
    setupEventListeners() {
        // Отслеживаем начало навигации
        window.addEventListener('navigateTo', () => {
            this.isNavigating = true;
            this.clearScrollTimeout();
        });

        // Отслеживаем завершение навигации
        window.addEventListener('stateUpdated', () => {
            setTimeout(() => {
                this.isNavigating = false;
            }, 100);
        });

        // Отслеживаем клики по пагинации
        document.addEventListener('click', (e) => {
            const paginationButton = e.target.closest('[data-pagination] button');
            if (paginationButton) {
                this.isPaginationClick = true;
                // Сбрасываем флаг после небольшой задержки
                setTimeout(() => {
                    this.isPaginationClick = false;
                }, 500);
            }
        });
    }

    /**
     * Настройка наблюдателя за изменениями пагинации
     */
    setupPageChangeObserver() {
        if (this.observer) return;

        this.observer = new MutationObserver(() => {
            this.checkPageChange();
        });

        this.observer.observe(document.body, {
            childList: true,
            subtree: true,
        });
    }

    /**
     * Проверка смены страницы
     */
    checkPageChange() {
        const activePage = this.getActivePageElement();

        if (activePage && this.lastActivePage !== activePage.textContent) {
            this.lastActivePage = activePage.textContent;

            // Прокручиваем вниз только если это клик по пагинации и не происходит навигация
            if (this.isPaginationClick && !this.isNavigating) {
                this.scrollToBottom();
            }
        }
    }

    /**
     * Получение элемента активной страницы
     */
    getActivePageElement() {
        return document.querySelector('[data-pagination] button.bg-gradient-to-r');
    }

    /**
     * Прокрутка страницы вниз
     */
    scrollToBottom() {
        this.clearScrollTimeout();

        this.scrollTimeout = setTimeout(() => {
            window.scrollTo({
                top: document.body.scrollHeight,
                behavior: 'smooth',
            });
        }, 100);
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
        if (this.observer) {
            this.observer.disconnect();
            this.observer = null;
        }

        this.clearScrollTimeout();
        this.initialized = false;
    }
}
