// app.js
import './bootstrap';

import { createIcons, icons } from 'lucide';

/**
 * Инициализация Lucide иконок внутри root
 */
function initLucide(root = document) {
    // ищем только <i data-lucide>, которые ещё не заменены
    const iconsToReplace = root.querySelectorAll('i[data-lucide]');
    if (iconsToReplace.length === 0) return;
    createIcons({ icons, root });
}

/**
 * Первый рендер при загрузке страницы
 */
document.addEventListener('DOMContentLoaded', () => {
    initLucide();
});

/**
 * Livewire SPA: после навигации
 */
document.addEventListener('livewire:navigated', () => {
    initLucide();
});

/**
 * Livewire: после обновления любого компонента
 */
document.addEventListener('livewire:rendered', (event) => {
    initLucide(event.target);
});

/**
 * MutationObserver для отслеживания добавления новых иконок
 * (например, когда Livewire вставляет HTML динамически)
 */
const observerRoot = document.body; // можно ограничить конкретным контейнером
if ('MutationObserver' in window) {
    const observer = new MutationObserver((mutationsList) => {
        let hasNewIcons = false;

        for (const mutation of mutationsList) {
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType === 1) {
                    // ELEMENT_NODE
                    if (node.matches('i[data-lucide]') || node.querySelector('i[data-lucide]')) {
                        hasNewIcons = true;
                    }
                }
            });
        }

        if (hasNewIcons) {
            initLucide(observerRoot);
        }
    });

    observer.observe(observerRoot, { childList: true, subtree: true });
}
