import './bootstrap';

import ColorisModule from './coloris';
import ConnectionStatus from './connection-status';
import Dropdown from './dropdown';
import CreateChecklistEditor from './editor/checklist-create';
import EditChecklistEditor from './editor/checklist-edit';
import CreateNoteEditor from './editor/note-create';
import EditNoteEditor from './editor/note-edit';
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
const editNoteEditor = new EditNoteEditor();
const createNoteEditor = new CreateNoteEditor();
const createChecklistEditor = new CreateChecklistEditor();
const editChecklistEditor = new EditChecklistEditor();

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
    editNoteEditor.init();
    createNoteEditor.init();
    createChecklistEditor.init();
    editChecklistEditor.init();

    initialized = true;
}

// Инициализация при обновлении состояния (только для нуждающихся модулей)
window.addEventListener('stateUpdated', () => {
    // Переинициализируем только те модули, которые это поддерживают
    if (pagination.reinit) pagination.reinit();
    if (search.reinit) search.reinit();
    if (dropdown.reinit) dropdown.reinit();
    if (notifications.reinit) notifications.reinit();
    if (coloris.reinit) coloris.reinit();
    if (lucide.reinit) lucide.reinit();
    if (sidebar.reinit) sidebar.reinit();
    if (editNoteEditor.reinit) editNoteEditor.reinit();
    if (createNoteEditor.reinit) createNoteEditor.reinit();
    if (createChecklistEditor.reinit) createChecklistEditor.reinit();
    if (editChecklistEditor.reinit) editChecklistEditor.reinit();
    // ConnectionStatus не переинициализируем, так как он должен работать постоянно
});

// Инициализация при готовности DOM
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll);
} else {
    initAll();
}
