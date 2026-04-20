<!-- Боковое меню NavigationSidebar -->
<aside id="navigation-sidebar"
    class="group fixed left-0 top-0 h-full z-[9999] flex flex-col bg-white border-r border-gray-200 shadow-xl transition-all duration-300 ease-in-out {{ $isExpanded ? 'w-64' : 'w-[72px]' }} hover:w-64 overflow-hidden"
    role="navigation" aria-label="Основное меню">
    <!-- Логотип -->
    <div class="flex items-center h-16 border-b border-gray-200 px-4 shrink-0">
        <div class="flex items-center justify-center w-10 h-10 flex-shrink-0">
            <x-logo></x-logo>
        </div>
        <h2
            class="ml-3 font-bold text-xl bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent whitespace-nowrap opacity-0 {{ $isExpanded ? 'opacity-100' : 'group-hover:opacity-100' }} transition-opacity duration-200">
            LeafNote</h2>
    </div>
    <!-- Навигация -->
    <nav id="sidebar-nav"
        class="flex-1 py-4 px-2 overflow-hidden {{ $isExpanded ? 'overflow-y-auto' : 'group-hover:overflow-y-auto' }}">
        <ul class="space-y-1">
            <!-- Основное меню -->
            <li>
                <h4
                    class="sidebar-section-title px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap overflow-hidden transition-all duration-200 {{ $isExpanded ? 'py-2 max-h-10' : 'py-0 max-h-0 group-hover:py-2 group-hover:max-h-10' }}">
                    Основное меню</h4>
                <ul class="mt-1 space-y-1">
                    <li>
                        <x-sidebar-item icon="layout-grid" label="Главная доска" wireClick="goTo('dashboard')"
                            :active="$section === 'dashboard'" :count="$this->noteCounts->dashboard" :isExpanded="$isExpanded" />
                    </li>
                    <li>
                        <x-sidebar-item icon="star" label="Избранное" wireClick="goTo('favorite')" :active="$section === 'favorite'"
                            :count="$this->noteCounts->favorite" :isExpanded="$isExpanded" />
                    </li>
                    <li>
                        <x-sidebar-item icon="lock" label="Сейф" wireClick="goTo('safe')" :active="$section === 'safe'"
                            :count="$this->noteCounts->safe" :isExpanded="$isExpanded" />
                    </li>
                    <li>
                        <x-sidebar-item icon="package" label="Архив" wireClick="goTo('archive')" :active="$section === 'archive'"
                            :count="$this->noteCounts->archive" :isExpanded="$isExpanded" />
                    </li>
                    <li>
                        <x-sidebar-item icon="trash" label="Корзина" wireClick="goTo('trash')" :active="$section === 'trash'"
                            :count="$this->trashCount" :isExpanded="$isExpanded" />
                    </li>
                </ul>
            </li>
            <!-- Разделитель -->
            <li class="my-2 mx-4 h-px bg-gray-200"></li>
            <!-- Действия -->
            <li>
                <h4
                    class="sidebar-section-title px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap overflow-hidden transition-all duration-200 {{ $isExpanded ? 'py-2 max-h-10' : 'py-0 max-h-0 group-hover:py-2 group-hover:max-h-10' }}">
                    Действия</h4>
                <ul class="mt-1 space-y-1">
                    <li>
                        <x-sidebar-item icon="file-plus" label="Создать заметку" wireClick="goTo('create-note')"
                            :active="$section === 'create-note'" :isExpanded="$isExpanded" />
                    </li>
                    <li>
                        <x-sidebar-item icon="list-plus" label="Создать список" wireClick="goTo('create-checklist')"
                            :active="$section === 'create-checklist'" :isExpanded="$isExpanded" />
                    </li>
                    <li>
                        <x-sidebar-item icon="folder-plus" label="Создать папку" wireClick="goTo('create-folder')"
                            :active="$section === 'create-folder'" :isExpanded="$isExpanded" />
                    </li>
                </ul>
            </li>
            <!-- Разделитель -->
            <li class="my-2 mx-4 h-px bg-gray-200"></li>
            <!-- Папки -->
            <li>
                <h4
                    class="sidebar-section-title px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap overflow-hidden transition-all duration-200 {{ $isExpanded ? 'py-2 max-h-10' : 'py-0 max-h-0 group-hover:py-2 group-hover:max-h-10' }}">
                    Папки</h4>
                <ul class="mt-1 space-y-1">
                    @foreach ($this->folders as $folder)
                        <li wire:key="folder-{{ $folder->id }}">
                            <x-sidebar-item icon="{{ $folder->icon }}" label="{{ $folder->title }}"
                                wireClick="goTo('folder', {{ $folder->id }})" :active="$section === 'folder' && $folderId == $folder->id" :count="$folder->notes_count"
                                :isExpanded="$isExpanded" />
                        </li>
                    @endforeach
                </ul>
            </li>
            <!-- Разделитель -->
            <li class="my-2 mx-4 h-px bg-gray-200"></li>
            <!-- Аккаунт -->
            <li>
                <h4
                    class="sidebar-section-title px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap overflow-hidden transition-all duration-200 {{ $isExpanded ? 'py-2 max-h-10' : 'py-0 max-h-0 group-hover:py-2 group-hover:max-h-10' }}">
                    Аккаунт</h4>
                <ul class="mt-1 space-y-1">
                    <li>
                        <x-sidebar-item icon="user" label="Профиль" wireClick="goTo('profile')" :active="$section === 'profile'"
                            :isExpanded="$isExpanded" />
                    </li>

                    <li>
                        <x-sidebar-item icon="log-out" label="Выйти" wireClick="confirmLogout" :active="$section === 'logout'"
                            :isExpanded="$isExpanded" />
                    </li>
                </ul>
            </li>
        </ul>
    </nav>

    <!-- Модальное окно подтверждения выхода -->
    <x-modal type="confirm" :show="$confirmingLogout" title="Вы хотите выйти из аккаунта?"
        description="Требуется подтверждение" icon="log-out" confirmText="Выйти" confirmMethod="logout"
        cancelMethod="closeLogoutModal" />
</aside>
@script
    <script>
        let collapseTimer = null;
        const sidebar = document.getElementById('navigation-sidebar');
        let isHovered = false;
        let isNavigating = false;

        function clearAllTimers() {
            if (collapseTimer) {
                clearTimeout(collapseTimer);
                collapseTimer = null;
            }
        }

        function updateExpandedAttribute() {
            if (sidebar) {
                const hasFullWidth = sidebar.classList.contains('w-64');
                const shouldShowScrollbar = hasFullWidth || isHovered;
                sidebar.setAttribute('data-expanded', shouldShowScrollbar ? 'true' : 'false');
            }
        }

        if (sidebar) {
            updateExpandedAttribute();
        }

        sidebar?.addEventListener('mouseenter', () => {
            isHovered = true;
            clearAllTimers();
            updateExpandedAttribute();
            // Если панель свёрнута через DOM (без Livewire) - показываем элементы
            const currentSidebar = document.getElementById('navigation-sidebar');
            if (currentSidebar && currentSidebar.classList.contains('w-[72px]')) {
                expandSidebarDOM();
            }
        });
        sidebar?.addEventListener('mouseleave', () => {
            isHovered = false;
            clearAllTimers();
            updateExpandedAttribute();
            collapseTimer = setTimeout(() => {
                if (isNavigating) {
                    return;
                }

                const currentSidebar = document.getElementById('navigation-sidebar');
                if (!currentSidebar || !document.contains(currentSidebar)) {
                    return;
                }

                try {
                    $wire.clearSidebarFlag();
                } catch (e) {
                    // Игнорируем ошибки если компонент уже уничтожен
                }
            }, 150);
        });

        // Перехватываем клики по ссылкам навигации и устанавливаем флаг ДО отправки запроса
        sidebar?.addEventListener('click', (e) => {
            const link = e.target.closest('a[wire\\:click]');
            if (link) {
                isNavigating = true;
                clearAllTimers();
            }
        });

        $wire.on('navigateTo', () => {
            // Флаг уже установлен в обработчике клика
            clearAllTimers();
            // После навигации сворачиваем через DOM
            setTimeout(() => {
                isNavigating = false;
                if (!isHovered) {
                    collapseSidebarDOM();
                }
            }, 250);
            // Центрируем активный элемент в скролле
            setTimeout(scrollToActiveItem, 300);
        });

        // Глобальный слушатель для события stateUpdated (отправляется из других компонентов)
        Livewire.on('stateUpdated', () => {
            // Центрируем активный элемент в скролле после обновления состояния
            setTimeout(scrollToActiveItem, 150);
        });

        function collapseSidebarDOM() {
            const currentSidebar = document.getElementById('navigation-sidebar');
            const sidebarNav = document.getElementById('sidebar-nav');

            if (currentSidebar && document.contains(currentSidebar)) {
                currentSidebar.classList.remove('w-64');
                currentSidebar.classList.add('w-[72px]');
                currentSidebar.setAttribute('data-expanded', 'false');

                if (sidebarNav && document.contains(sidebarNav)) {
                    sidebarNav.classList.remove('overflow-y-auto');
                    sidebarNav.classList.add('overflow-hidden');
                }

                currentSidebar.querySelectorAll('.opacity-100').forEach(el => {
                    el.classList.remove('opacity-100');
                    el.classList.add('opacity-0');
                });

                // Сворачиваем заголовки секций
                currentSidebar.querySelectorAll('.sidebar-section-title').forEach(el => {
                    el.classList.remove('py-2', 'max-h-10');
                    el.classList.add('py-0', 'max-h-0');
                });
            }
        }

        function expandSidebarDOM() {
            const currentSidebar = document.getElementById('navigation-sidebar');
            const sidebarNav = document.getElementById('sidebar-nav');

            if (currentSidebar && document.contains(currentSidebar)) {
                currentSidebar.classList.remove('w-[72px]');
                currentSidebar.classList.add('w-64');
                currentSidebar.setAttribute('data-expanded', 'true');

                if (sidebarNav && document.contains(sidebarNav)) {
                    sidebarNav.classList.remove('overflow-hidden');
                    sidebarNav.classList.add('overflow-y-auto');
                }

                currentSidebar.querySelectorAll('.opacity-0').forEach(el => {
                    el.classList.remove('opacity-0');
                    el.classList.add('opacity-100');
                });

                // Разворачиваем заголовки секций
                currentSidebar.querySelectorAll('.sidebar-section-title').forEach(el => {
                    el.classList.remove('py-0', 'max-h-0');
                    el.classList.add('py-2', 'max-h-10');
                });
            }
        }
        document.addEventListener('beforeunload', clearAllTimers);
        let scrollTimeout;
        const STORAGE_KEY = 'sidebar_scroll';

        function handleScroll() {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                const nav = document.getElementById('sidebar-nav');
                if (nav) {
                    localStorage.setItem(STORAGE_KEY, nav.scrollTop);
                }
            }, 100);
        }

        function scrollToActiveItem() {
            const nav = document.getElementById('sidebar-nav');
            if (!nav) {
                setTimeout(scrollToActiveItem, 50);
                return;
            }

            const activeLink = nav.querySelector('.sidebar-active-item');
            if (!activeLink) return;

            const navRect = nav.getBoundingClientRect();
            const linkRect = activeLink.getBoundingClientRect();

            const scrollTop = nav.scrollTop + linkRect.top - navRect.top - (navRect.height / 2) + (linkRect.height / 2);

            nav.scrollTo({
                top: scrollTop,
                behavior: 'smooth'
            });
        }


        function setupScrollListenerAndRestore() {
            const nav = document.getElementById('sidebar-nav');
            if (!nav) {
                setTimeout(setupScrollListenerAndRestore, 50);
                return;
            }
            const savedScroll = localStorage.getItem(STORAGE_KEY);
            if (savedScroll !== null) {
                requestAnimationFrame(() => {
                    nav.scrollTop = parseInt(savedScroll, 10);
                });
            }
            nav.removeEventListener('scroll', handleScroll);
            nav.addEventListener('scroll', handleScroll);

            setTimeout(scrollToActiveItem, 200);
        }

        setupScrollListenerAndRestore();
    </script>
@endscript
