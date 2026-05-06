import { createIcons, icons } from 'lucide';

/**
 * Класс для инициализации и наблюдения за иконками Lucide
 */
export default class Lucide {
    constructor() {
        this.iconObserver = null;
    }

    /**
     * Инициализация иконок Lucide в указанном корневом элементе
     * @param {Document|Element} root - Корневой элемент для поиска иконок (по умолчанию document)
     */
    initLucide(root = document) {
        const iconsToReplace = root.querySelectorAll('i[data-lucide]');
        if (iconsToReplace.length === 0) return;
        createIcons({ icons, root });
    }

    /**
     * Настройка наблюдателя за изменениями в DOM для автоматического обновления иконок
     */
    setupIconObserver() {
        if (this.iconObserver) return;

        this.iconObserver = new MutationObserver((mutationsList) => {
            let hasNewIcons = false;

            for (const mutation of mutationsList) {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) {
                        if (
                            node.matches('i[data-lucide]') ||
                            node.querySelector('i[data-lucide]')
                        ) {
                            hasNewIcons = true;
                        }
                    }
                });
            }

            if (hasNewIcons) {
                this.initLucide(document.body);
            }
        });

        this.iconObserver.observe(document.body, {
            childList: true,
            subtree: true,
        });
    }

    /**
     * Инициализация модуля: первоначальная установка иконок и запуск наблюдателя
     */
    init() {
        this.initLucide();
        this.setupIconObserver();
    }
}
