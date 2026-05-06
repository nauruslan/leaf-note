import './bootstrap';
import './editor/create-note-editor';
import './editor/note-view-editor';
import './editor/checklist-create';
import './editor/checklist-edit';
import './dropdown';
import './toggle';
import './notifications';
import './coloris';
import './app-init';

// import './sidebar';
import { createIcons, icons } from 'lucide';

// document.addEventListener('DOMContentLoaded', () => {
//     createIcons({ icons });
// });

// window.addEventListener('unhandledrejection', (event) => {
//     const error = event.reason;

//     // Это как раз тот самый объект Livewire: {status: null, body: null, json: null, errors: null}
//     const isLivewireNullError =
//         error &&
//         typeof error === 'object' &&
//         error.status === null &&
//         error.body === null &&
//         error.json === null &&
//         error.errors === null;

//     if (isLivewireNullError) {
//         // Глушим его, чтобы не засорял консоль
//         event.preventDefault();
//     }
// });

/**
 * Перехват Livewire ошибок, когда сервер недоступен
 * (но НЕ ломаем Livewire и НЕ трогаем fetch)
 */
// window.addEventListener('unhandledrejection', (event) => {
//     const error = event.reason;

//     // Livewire internal error — игнорируем
//     const isLivewireNullError =
//         error &&
//         typeof error === 'object' &&
//         error.status === null &&
//         error.body === null &&
//         error.json === null &&
//         error.errors === null;

//     if (isLivewireNullError) {
//         event.preventDefault();
//         return;
//     }

//     // Ошибка "сервер недоступен"
//     const message = String(error?.message || '');

//     const isServerDown =
//         message.includes('ERR_CONNECTION_REFUSED') ||
//         message.includes('Failed to fetch') ||
//         message.includes('NetworkError') ||
//         message.includes('net::ERR_CONNECTION_REFUSED');

//     if (isServerDown) {
//         // Включаем оффлайн
//         this.goOffline?.();
//         event.preventDefault();
//     }
// });

function initLucide(root = document) {
    const iconsToReplace = root.querySelectorAll('i[data-lucide]');
    if (iconsToReplace.length === 0) return;
    createIcons({ icons, root });
}

// Делаем доступным глобально для других модулей (например, safe-timer)
window.initLucide = initLucide;

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
