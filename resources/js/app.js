// app.js
import './bootstrap';
import './editor/create-note-editor';
import './editor/note-view-editor';

import { createIcons, icons } from 'lucide';

function initLucide(root = document) {
    const iconsToReplace = root.querySelectorAll('i[data-lucide]');
    if (iconsToReplace.length === 0) return;
    createIcons({ icons, root });
}

document.addEventListener('DOMContentLoaded', () => {
    initLucide();
});

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

setupIconObserver();
