// app.js
import './bootstrap';

import { createIcons, icons } from 'lucide';

import { initCreateNoteEditor } from './editor/create-note-editor';

/**
 * Инициализация Lucide иконок
 */
function initLucide(root = document) {
    const iconsToReplace = root.querySelectorAll('i[data-lucide]');
    if (iconsToReplace.length === 0) return;
    createIcons({ icons, root });
}

// Первый рендер при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    initLucide();
    initCreateNoteEditor();
});

document.addEventListener('livewire:load', () => {
    Livewire.hook('afterNavigate', () => {
        // повторная инициализация ваших библиотек
        initLucide();
        initCreateNoteEditor();
    });
});

// Livewire SPA: после навигации
document.addEventListener('livewire:navigated', () => {
    initLucide();
    // initCreateNoteEditor();
});

// Livewire: после обновления любого компонента
document.addEventListener('livewire:rendered', (event) => {
    initLucide(event.target);
    // initCreateNoteEditor(event.target);
});

// MutationObserver для динамически добавляемых иконок
if ('MutationObserver' in window) {
    const observer = new MutationObserver((mutationsList) => {
        let hasNewIcons = false;
        for (const mutation of mutationsList) {
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType === 1) {
                    if (node.matches('i[data-lucide]') || node.querySelector('i[data-lucide]')) {
                        hasNewIcons = true;
                    }
                }
            });
        }
        if (hasNewIcons) {
            initLucide(document.body);
        }
    });
    observer.observe(document.body, { childList: true, subtree: true });
}
