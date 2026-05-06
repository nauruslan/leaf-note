/**
 * Обработка потери интернет-соединения
 */

// Флаг для отслеживания состояния соединения
let isAppOffline = false;
let offlineTimeout = null;
let consecutiveErrors = 0;

// Получаем элемент оверлея
const getOverlay = () => document.getElementById('connection-status-overlay');

/**
 * Показать оверлей потери соединения
 */
function showOfflineOverlay() {
    const overlay = getOverlay();
    if (overlay) {
        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
    }
}

/**
 * Скрыть оверлей потери соединения
 */
function hideOfflineOverlay() {
    const overlay = getOverlay();
    if (overlay) {
        overlay.classList.add('hidden');
        overlay.classList.remove('flex');
    }
}

/**
 * Инициализация обработчиков событий
 */
function initialize() {
    // Отслеживание событий браузера offline/online
    window.addEventListener('offline', () => {
        if (!isAppOffline) {
            isAppOffline = true;
            showOfflineOverlay();
        }
    });

    window.addEventListener('online', () => {
        if (isAppOffline) {
            isAppOffline = false;
            hideOfflineOverlay();
        }
    });

    /**
     * Патчим глобальный fetch для перехвата сетевых ошибок Livewire 4
     * В Livewire 4 нет Livewire.onError(), поэтому используем fetch interceptor
     */
    const originalFetch = window.fetch;

    window.fetch = async (...args) => {
        const url = args[0] || '';

        // Если мы в оффлайн-режиме и это Livewire запрос, блокируем его
        if (isAppOffline && typeof url === 'string' && url.includes('/livewire-')) {
            return Promise.reject(new Error('NetworkError: Connection refused'));
        }

        try {
            const response = await originalFetch(...args);

            // Сбрасываем счётчик ошибок при успешном запросе
            consecutiveErrors = 0;

            // Если запрос успешен и мы были в оффлайн-режиме, восстанавливаемся
            if (response.ok && isAppOffline) {
                // Очищаем таймаут, если есть
                if (offlineTimeout) {
                    clearTimeout(offlineTimeout);
                    offlineTimeout = null;
                }

                // Задержка перед восстановлением, чтобы избежать ложных срабатываний
                offlineTimeout = setTimeout(() => {
                    isAppOffline = false;
                    hideOfflineOverlay();
                }, 500);
            }

            return response;
        } catch (error) {
            // Проверяем, что это именно сетевая ошибка
            const errorMessage = error.message || '';
            const isNetworkError =
                errorMessage.includes('NetworkError') ||
                errorMessage.includes('Failed to fetch') ||
                errorMessage.includes('fetch failed') ||
                errorMessage.includes('ERR_INTERNET_DISCONNECTED') ||
                errorMessage.includes('ERR_NETWORK') ||
                errorMessage.includes('ERR_CONNECTION_REFUSED') ||
                errorMessage.includes('ERR_NAME_NOT_RESOLVED') ||
                errorMessage.includes('ECONNREFUSED');

            if (isNetworkError) {
                consecutiveErrors++;

                // Показываем оверлей после 2-х последовательных ошибок
                if (consecutiveErrors >= 2 && !isAppOffline) {
                    isAppOffline = true;
                    showOfflineOverlay();
                }
            }

            // Перебрасываем ошибку дальше
            throw error;
        }
    };
}

// Инициализация при готовности DOM
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialize);
} else {
    initialize();
}
