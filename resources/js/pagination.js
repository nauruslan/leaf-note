/**
 * Автоматическая прокрутка вниз при смене страницы пагинации
 */
export default class Pagination {
    constructor() {
        this.initialized = false;
        this.observer = null;
        this.lastActivePage = null;
    }

    /**
     * Инициализация модуля
     */
    init() {
        if (this.initialized) return;

        this.setupPageChangeObserver();
        this.initialized = true;
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
            this.scrollToBottom();
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
        setTimeout(() => {
            window.scrollTo({
                top: document.body.scrollHeight,
            });
        }, 0);
    }
}
