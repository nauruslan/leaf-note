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
        const el = document.getElementById('notifications-container');
        if (!el || this.initialized) return;

        this.container = el;
        this.initialized = true;

        if (window.Livewire) {
            Livewire.on('notification', (data) => {
                this.show(data.title, data.content, data.type, 3000);
            });
        }
    }

    show(title, content, type, duration) {
        if (!this.initialized) this.init();
        if (!this.container) return;

        const notification = document.createElement('div');
        notification.className =
            'notification-item overflow-hidden rounded-xl bg-white/95 dark:bg-slate-800/95 border-l-4 shadow-xl transition-all duration-300';
        notification.style.maxWidth = '400px';
        notification.style.minWidth = '300px';
        notification.style.transform = 'translateX(120%)';
        notification.style.opacity = '0';

        const borderColor = this.borderColors[type] || this.borderColors.info;
        const progressColor = this.progressColors[type] || this.progressColors.info;
        const iconName = this.icons[type] || this.icons.info;
        const iconColor = this.iconColors[type] || this.iconColors.info;

        notification.classList.add(borderColor);

        notification.innerHTML =
            '<div class="flex items-start gap-3 p-4">' +
            '<div class="flex-shrink-0">' +
            '<i data-lucide="' +
            iconName +
            '" class="w-5 h-5 ' +
            iconColor +
            '"></i>' +
            '</div>' +
            '<div class="flex-1 min-w-0">' +
            (title
                ? '<div class="font-semibold text-sm text-slate-800 dark:text-slate-100">' +
                  title +
                  '</div>'
                : '') +
            '<div class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">' +
            content +
            '</div>' +
            '</div>' +
            '<button class="notification-close flex-shrink-0 p-1 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 transition-all duration-150 cursor-pointer bg-transparent border-none">' +
            '<i data-lucide="x" class="w-4 h-4"></i>' +
            '</button>' +
            '</div>' +
            '<div class="absolute bottom-0 left-0 w-full h-[3px] bg-slate-200 dark:bg-slate-700 overflow-hidden rounded-b-xl">' +
            '<div class="notification-progress h-full origin-left ' +
            progressColor +
            '" style="transition: transform ' +
            duration +
            'ms linear"></div>' +
            '</div>';

        this.container.insertBefore(notification, this.container.firstChild);

        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                notification.style.transform = 'translateX(0)';
                notification.style.opacity = '1';

                const progress = notification.querySelector('.notification-progress');
                requestAnimationFrame(() => {
                    progress.style.transform = 'scaleX(0)';
                });
            });
        });

        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.addEventListener('click', () => {
            this.close(notification);
        });

        const timeoutId = setTimeout(() => {
            this.close(notification);
        }, duration);

        notification.dataset.timeoutId = timeoutId;
        this.updatePositions();
    }

    close(notification) {
        if (!notification || !notification.parentNode) return;

        const timeoutId = notification.dataset.timeoutId;
        if (timeoutId) clearTimeout(parseInt(timeoutId));

        notification.style.transform = 'translateX(120%)';
        notification.style.opacity = '0';

        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
                this.updatePositions();
            }
        }, 300);
    }

    updatePositions() {
        if (!this.container) return;
        const notifications = this.container.querySelectorAll('.notification-item');
        for (let i = 0; i < notifications.length; i++) {
            notifications[i].style.marginTop = i * 10 + 'px';
        }
    }
}

const notifications = new Notifications();

// Основная инициализация
function initialize() {
    notifications.init();
}

// Инициализация при готовности DOM
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialize);
} else {
    initialize();
}
