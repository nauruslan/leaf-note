export function initToggles(selector = '.toggle-switch input') {
    const toggles = document.querySelectorAll(selector);

    toggles.forEach((toggle) => {
        // Восстанавливаем состояние из localStorage
        const storageKey = `toggleState_${toggle.id || toggle.name || toggle.dataset.key || 'default'}`;
        const savedState = localStorage.getItem(storageKey);
        if (savedState === 'true') {
            toggle.checked = true;
        }

        // Слушаем изменения
        toggle.addEventListener('change', () => {
            localStorage.setItem(storageKey, toggle.checked);

            // Диспатч кастомного события
            toggle.dispatchEvent(
                new CustomEvent('toggle-change', {
                    detail: { checked: toggle.checked, element: toggle },
                }),
            );
        });
    });
}

// Автоматическая инициализация при загрузке DOM
document.addEventListener('DOMContentLoaded', () => {
    initToggles();
});
