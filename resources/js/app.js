// app.js
import './bootstrap';

import { createIcons, icons } from 'lucide';

import { initCreateNoteEditor } from './editor/create-note-editor';

// Для разработки
Livewire.hook('element.init', ({ component }) => {
    console.log('🔍 component object:', {
        name: component.name,
        id: component.id,
    });
});

function initLucide(root = document) {
    const iconsToReplace = root.querySelectorAll('i[data-lucide]');
    if (iconsToReplace.length === 0) return;
    createIcons({ icons, root });
}

document.addEventListener('DOMContentLoaded', () => {
    initLucide();
    initCreateNoteEditor();
});

let editorObserver = null;

function setupEditorObserver() {
    if (editorObserver) return;

    editorObserver = new MutationObserver((mutations) => {
        for (const mutation of mutations) {
            for (const node of mutation.addedNodes) {
                if (node.nodeType === 1) {
                    if (node.id === 'editor' || node.querySelector?.('#editor')) {
                        setTimeout(() => {
                            initLucide(document.body);
                            initCreateNoteEditor();
                        }, 10);
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
