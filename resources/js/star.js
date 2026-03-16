document.addEventListener('click', (e) => {
    const btn = e.target.closest('.favorite-btn');
    if (!btn) return;
    btn.classList.toggle('active');
});
