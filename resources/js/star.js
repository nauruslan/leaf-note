document.addEventListener('click', (e) => {
    const btn = e.target.closest('.favorite-btn');
    if (!btn) return;
    // Если есть атрибут wire:click или wire:model, не переключаем класс, пусть Livewire управляет
    if (btn.hasAttribute('wire:click') || btn.hasAttribute('wire:model')) {
        return;
    }
    btn.classList.toggle('active');
});
