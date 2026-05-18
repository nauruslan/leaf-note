export default class ConnectionStatus {
    constructor() {
        this.initialized = false;

        this.isAppOffline = false;
        this.consecutiveErrors = 0;

        this.overlaySelector = '#connection-status-overlay';
    }

    /**
     * Инициализация
     */
    init() {
        if (this.initialized) return;
        this.initialized = true;

        this.setupOfflineOnlineListeners();
        this.patchFetchOnce();
    }

    /**
     * Переинициализация
     */
    reinit() {}

    getOverlay() {
        return document.querySelector(this.overlaySelector);
    }

    showOfflineOverlay() {
        const overlay = this.getOverlay();
        if (overlay) {
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
        }
    }

    hideOfflineOverlay() {
        const overlay = this.getOverlay();
        if (overlay) {
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
        }
    }

    /**
     * Реакция на системные offline/online события браузера
     */
    setupOfflineOnlineListeners() {
        window.addEventListener('offline', () => {
            if (!this.isAppOffline) {
                this.goOffline();
            }
        });

        window.addEventListener('online', () => {
            // Появилось физическое подключение — делаем одну проверку сервера
            if (this.isAppOffline) {
                this.checkServer();
            }
        });
    }

    goOffline() {
        if (this.isAppOffline) return;

        this.isAppOffline = true;
        this.showOfflineOverlay();
    }

    goOnline() {
        if (!this.isAppOffline) return;

        this.isAppOffline = false;
        this.hideOfflineOverlay();
    }

    /**
     * Однократная проверка доступности сервера.
     */
    async checkServer() {
        // Пока браузер офлайн — не шлём запрос
        if (!navigator.onLine) {
            return;
        }

        try {
            await fetch(window.location.href, {
                method: 'HEAD',
                mode: 'no-cors',
                cache: 'no-cache',
                keepalive: false,
            });

            // Сервер ответил — восстанавливаемся
            this.goOnline();
        } catch {
            // Сервер пока недоступен
        }
    }

    /**
     * Патчим fetch строго один раз
     */
    patchFetchOnce() {
        if (window.__connectionStatusFetchPatched) return;
        window.__connectionStatusFetchPatched = true;

        const originalFetch = window.fetch;
        const self = this;

        window.fetch = async (...args) => {
            const url = args[0] || '';

            // Livewire‑запросы
            const isLivewireRequest =
                typeof url === 'string' &&
                (url.includes('/livewire-') ||
                    url.includes('/livewire/') ||
                    url.includes('/livewire/update') ||
                    url.includes('/livewire/message'));

            // Блокируем Livewire‑запросы, пока оффлайн
            if (self.isAppOffline && isLivewireRequest) {
                return Promise.reject(new Error('NetworkError: Connection refused'));
            }

            try {
                const response = await originalFetch(...args);

                // Успешный запрос → сброс ошибок
                self.consecutiveErrors = 0;

                // Если были оффлайн — восстанавливаемся
                if (response.ok && self.isAppOffline) {
                    self.goOnline();
                }

                return response;
            } catch (error) {
                const msg = error.message || '';

                const isNetworkError =
                    msg.includes('NetworkError') ||
                    msg.includes('Failed to fetch') ||
                    msg.includes('fetch failed') ||
                    msg.includes('ERR_INTERNET_DISCONNECTED') ||
                    msg.includes('ERR_NETWORK') ||
                    msg.includes('ERR_CONNECTION_REFUSED') ||
                    msg.includes('ERR_NAME_NOT_RESOLVED') ||
                    msg.includes('ECONNREFUSED');

                if (isNetworkError) {
                    self.consecutiveErrors++;

                    // После 2 ошибок → оффлайн
                    if (self.consecutiveErrors >= 2 && !self.isAppOffline) {
                        self.goOffline();
                    }
                }

                throw error;
            }
        };
    }
}
