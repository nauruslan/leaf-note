// app.js
import './bootstrap';

import { createIcons, icons } from 'lucide';

import { initCreateNoteEditor } from './editor/create-note-editor';
import { initNoteViewEditor } from './editor/note-view-editor';

// Для разработки
// Livewire.hook('element.init', ({ component }) => {
//     console.log('🔍 component object:', {
//         name: component.name,
//         id: component.id,
//     });
// });

function initLucide(root = document) {
    const iconsToReplace = root.querySelectorAll('i[data-lucide]');
    if (iconsToReplace.length === 0) return;
    createIcons({ icons, root });
}

document.addEventListener('DOMContentLoaded', () => {
    initLucide();
    // Редакторы инициализируются через MutationObserver при добавлении #editor в DOM
});

let editorObserver = null;
let isCreateNoteEditorInitialized = false;
let isNoteViewEditorInitialized = false;

// Сбрасываем флаги при навигации Livewire
document.addEventListener('livewire:navigating', () => {
    isCreateNoteEditorInitialized = false;
    isNoteViewEditorInitialized = false;
});

function setupEditorObserver() {
    if (editorObserver) return;

    editorObserver = new MutationObserver((mutations) => {
        for (const mutation of mutations) {
            for (const node of mutation.addedNodes) {
                if (node.nodeType === 1) {
                    if (
                        node.id === 'note-view-editor' ||
                        node.id === 'create-note-editor' ||
                        node.querySelector?.('#note-view-editor') ||
                        node.querySelector?.('#create-note-editor')
                    ) {
                        setTimeout(() => {
                            initLucide(document.body);

                            // Простой способ: проверяем заголовок страницы
                            const pageTitle =
                                document.querySelector('h1')?.textContent?.trim() || '';

                            if (
                                pageTitle.includes('Просмотр') ||
                                pageTitle.includes('Редактирование')
                            ) {
                                if (
                                    !isNoteViewEditorInitialized &&
                                    typeof initNoteViewEditor === 'function'
                                ) {
                                    initNoteViewEditor();
                                    isNoteViewEditorInitialized = true;
                                    isCreateNoteEditorInitialized = false;
                                }
                            } else if (
                                pageTitle.includes('Создать') ||
                                pageTitle.includes('Создание')
                            ) {
                                if (
                                    !isCreateNoteEditorInitialized &&
                                    typeof initCreateNoteEditor === 'function'
                                ) {
                                    initCreateNoteEditor();
                                    isCreateNoteEditorInitialized = true;
                                    isNoteViewEditorInitialized = false;
                                }
                            } else {
                                if (typeof initCreateNoteEditor === 'function') {
                                    initCreateNoteEditor();
                                }
                                if (typeof initNoteViewEditor === 'function') {
                                    initNoteViewEditor();
                                }
                            }
                        }, 50);
                        return;
                    }
                }
            }
        }
    });

    editorObserver.observe(document.body, {
        childList: true,
        subtree: true,
    });
}

let iconObserver = null;

function setupIconObserver() {
    if (iconObserver) return;

    iconObserver = new MutationObserver((mutationsList) => {
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

    iconObserver.observe(document.body, {
        childList: true,
        subtree: true,
    });
}

setupEditorObserver();
setupIconObserver();
