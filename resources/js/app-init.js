/**
 * Единый файл инициализации всех JS-модулей
 */
import ColorisModule from './coloris';
import ConnectionStatus from './connection-status';
import Dropdown from './dropdown';
import Lucide from './lucide';
import Notifications from './notifications';
import Pagination from './pagination';
import Search from './search';
import Sidebar from './sidebar';

// Создаём экземпляры модулей
const pagination = new Pagination();
const search = new Search();
const dropdown = new Dropdown();
const notifications = new Notifications();
const coloris = new ColorisModule();
const connectionStatus = new ConnectionStatus();
const lucide = new Lucide();
const sidebar = new Sidebar();

// Флаг для отслеживания инициализации
let initialized = false;

/**
 * Инициализация всех модулей
 */
function initAll() {
    if (initialized) return; // Предотвращаем повторную инициализацию

    pagination.init();
    search.init();
    dropdown.init();
    notifications.init();
    coloris.init();
    connectionStatus.init();
    lucide.init();
    sidebar.init();

    initialized = true;
}

// Инициализация при инициализации Livewire
document.addEventListener('livewire:init', initAll);

// Для остальных модулей, которые могут нуждаться в переинициализации
function reinitIfNeeded() {
    // Переинициализируем только те модули, которые это поддерживают
    if (pagination.reinit) pagination.reinit();
    if (search.reinit) search.reinit();
    if (dropdown.reinit) dropdown.reinit();
    if (notifications.reinit) notifications.reinit();
    if (coloris.reinit) coloris.reinit();
    if (lucide.reinit) lucide.reinit();
    if (sidebar.reinit) sidebar.reinit();
    // ConnectionStatus не переинициализируем, так как он должен работать постоянно
}

// Инициализация при обновлении Livewire (только для нуждающихся модулей)
document.addEventListener('livewire:updated', reinitIfNeeded);

// Инициализация при обновлении состояния (только для нуждающихся модулей)
window.addEventListener('stateUpdated', reinitIfNeeded);

// Инициализация при готовности DOM (для случаев без Livewire)
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll);
} else {
    initAll();
}
