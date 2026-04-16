class Notifications {
    constructor() {
        this.container = null;
        this.initialized = false;

        this.borderColors = {
            success: 'border-emerald-500',
            warning: 'border-amber-500',
            danger: 'border-red-500',
            info: 'border-blue-500',
        };

        this.bgColors = {
            success: 'bg-emerald-50 dark:bg-emerald-900',
            warning: 'bg-amber-50 dark:bg-amber-900',
            danger: 'bg-red-50 dark:bg-red-900',
            info: 'bg-blue-50 dark:bg-blue-900',
        };

        this.progressColors = {
            success: 'bg-emerald-500',
            warning: 'bg-amber-500',
            danger: 'bg-red-500',
            info: 'bg-blue-500',
        };

        this.icons = {
            success: 'check-circle',
            warning: 'alert-triangle',
            danger: 'alert-circle',
            info: 'info',
        };

        this.iconColors = {
            success: 'text-emerald-500',
            warning: 'text-amber-500',
            danger: 'text-red-500',
            info: 'text-blue-500',
        };
    }

    init() {
        if (this.initialized) return;

        this.container = document.getElementById('notifications-container');
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.id = 'notifications-container';
            this.container.className =
                'fixed top-5 right-[2%] z-[999999] pointer-events-none flex flex-col items-end gap-2.5';
            document.body.appendChild(this.container);
        }

        this.initialized = true;

        const bindEvent = () => {
            if (window.Livewire) {
                Livewire.on('notification', (data) => {
                    // Проверяем, включены ли уведомления в настройках пользователя
                    if (window.App && window.App.notificationsEnabled === false) {
                        return;
                    }
                    this.show(data.title, data.content, data.type, data.duration || 3000);
                });

                // Слушаем событие обновления настроек уведомлений
                Livewire.on('notifications-settings-updated', (data) => {
                    if (window.App) {
                        window.App.notificationsEnabled = data.enabled;
                    }
                });
            }
        };

        if (window.Livewire) {
            bindEvent();
        } else {
            document.addEventListener('livewire:init', bindEvent);
        }
    }

    show(title, content, type, duration) {
        if (!this.initialized) this.init();
        if (!this.container) return;

        const notification = document.createElement('div');
        const borderColor = this.borderColors[type] || this.borderColors.info;
        const bgColor = this.bgColors[type] || this.bgColors.info;
        const progressColor = this.progressColors[type] || this.progressColors.info;
        const iconName = this.icons[type] || this.icons.info;
        const iconColor = this.iconColors[type] || this.iconColors.info;

        notification.className = `notification-item relative overflow-hidden rounded-xl ${bgColor} ${borderColor} border-l-4 shadow-xl`;

        notification.style.cssText = `
            max-width: 400px; min-width: 300px; opacity: 1; z-index: 999999;
            isolation: isolate; transform: translate3d(120%, 0, 0);
            will-change: transform; transition: transform 0.3s cubic-bezier(0.2, 0, 0, 1);
        `;

        notification.innerHTML = `
            <div class="flex items-start gap-3 p-4 pointer-events-auto">
                <div class="flex-shrink-0">
                    <i data-lucide="${iconName}" class="w-5 h-5 ${iconColor}"></i>
                </div>
                <div class="flex-1 min-w-0">
                    ${title ? `<div class="font-semibold text-sm text-slate-800 dark:text-slate-100">${title}</div>` : ''}
                    <div class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">${content}</div>
                </div>
                <button class="notification-close flex-shrink-0 p-1 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors cursor-pointer bg-transparent border-none" aria-label="Закрыть">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            <div class="absolute bottom-0 left-0 w-full h-[3px] bg-slate-200/50 dark:bg-slate-700/50 overflow-hidden rounded-b-xl">
                <div class="notification-progress h-full origin-left ${progressColor}" style="transform: scaleX(1); transition: transform ${duration}ms linear;"></div>
            </div>`;

        this.container.insertBefore(notification, this.container.firstChild);

        requestAnimationFrame(() => {
            notification.style.transform = 'translate3d(0, 0, 0)';
            const progress = notification.querySelector('.notification-progress');
            if (progress) {
                requestAnimationFrame(() => {
                    progress.style.transform = 'scaleX(0)';
                });
            }
        });

        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.addEventListener('click', () => this.close(notification));

        const timeoutId = setTimeout(() => this.close(notification), duration);
        notification.dataset.timeoutId = timeoutId;

        this.updatePositions();
    }

    close(notification) {
        if (!notification || !notification.parentNode) return;
        const timeoutId = notification.dataset.timeoutId;
        if (timeoutId) clearTimeout(parseInt(timeoutId));

        notification.style.transform = 'translate3d(120%, 0, 0)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
                this.updatePositions();
            }
        }, 300);
    }

    updatePositions() {
        if (!this.container) return;
        const items = this.container.querySelectorAll('.notification-item');
        items.forEach((item, i) => {
            item.style.marginTop = `${i * 10}px`;
        });
    }
}

const notifications = new Notifications();

function initialize() {
    notifications.init();
}
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialize);
} else {
    initialize();
}

// Статический массив всех динамических классов.
// Гарантирует, что компилятор Tailwind не вырежет их при сборке.
const __tw_scan_block__ = [
    'bg-emerald-50',
    'dark:bg-emerald-900',
    'bg-amber-50',
    'dark:bg-amber-900',
    'bg-red-50',
    'dark:bg-red-900',
    'bg-blue-50',
    'dark:bg-blue-900',
    'border-emerald-500',
    'border-amber-500',
    'border-red-500',
    'border-blue-500',
    'text-emerald-500',
    'text-amber-500',
    'text-red-500',
    'text-blue-500',
    'hover:text-red-500',
    'hover:bg-red-50',
    'dark:hover:bg-red-900/30',
    'bg-slate-200/50',
    'dark:bg-slate-700/50',
    'z-[999999]',
    'h-[3px]',
    'pointer-events-none',
    'pointer-events-auto',
    'isolation-isolate',
    'origin-left',
    'rounded-b-xl',
    'border-l-4',
    'shadow-xl',
    'overflow-hidden',
];
