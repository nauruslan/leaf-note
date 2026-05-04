/**
 * Автоматическая прокрутка вниз при смене страницы пагинации
 */
document.addEventListener('livewire:init', () => {
    // Слушаем событие смены страницы от Livewire
    Livewire.on('page-changed', () => {
        scrollToBottom();
    });
});

/**
 * Прокрутка страницы вниз
 */
function scrollToBottom() {
    // Используем setTimeout для гарантии, что DOM обновлён
    setTimeout(() => {
        window.scrollTo({
            top: document.body.scrollHeight,
            behavior: 'smooth',
        });
    }, 50);
}
