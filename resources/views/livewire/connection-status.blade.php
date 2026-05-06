<div>
    <div id="connection-status-overlay"
        class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/70 backdrop-blur-md transition-opacity duration-300"
        x-cloak>
        <div class="flex flex-col items-center gap-6 text-center">
            <!-- Заголовок -->
            <h2 class="text-3xl font-semibold text-white">
                Нет соединения
            </h2>

            <!-- Подзаголовок -->
            <p class="text-lg text-white/80">
                Проверьте интернет и попробуйте снова
            </p>

            <!-- Анимация загрузки (spinner) -->
            <div class="h-8 w-8 animate-spin rounded-full border-4 border-white/30 border-t-white"></div>
        </div>
    </div>
</div>
