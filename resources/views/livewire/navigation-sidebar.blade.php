<!-- Боковое меню NavigationSidebar -->
<aside id="navigation-sidebar"
    class="group fixed left-0 top-0 h-full z-[9999] flex flex-col bg-white border-r border-gray-200 shadow-xl transition-all duration-300 ease-in-out {{ $isExpanded ? 'w-64' : 'hover:w-64 w-[72px]' }} overflow-hidden"
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
                    class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap opacity-0 {{ $isExpanded ? 'opacity-100' : 'group-hover:opacity-100' }} transition-opacity duration-200">
                    Основное меню</h4>
                <ul class="mt-1 space-y-1">
                    <li>
                        <x-sidebar-item icon="layout-grid" label="Главная доска" wireClick="goTo('dashboard')"
                            :active="$section === 'dashboard'" :count="$this->noteCounts->dashboardCount" :isExpanded="$isExpanded" />
                    </li>
                    <li>
                        <x-sidebar-item icon="clipboard-list" label="Списки задач" wireClick="goTo('checklist')"
                            :active="$section === 'checklist'" :count="$this->noteCounts->checklistCount" :isExpanded="$isExpanded" />
                    </li>
                    <li>
                        <x-sidebar-item icon="star" label="Избранное" wireClick="goTo('favorite')" :active="$section === 'favorite'"
                            :count="$this->noteCounts->favoriteCount" :isExpanded="$isExpanded" />
                    </li>
                    <li>
                        <x-sidebar-item icon="lock" label="Сейф" wireClick="goTo('safe')" :active="$section === 'safe'"
                            :count="$this->noteCounts->safeCount" :isExpanded="$isExpanded" />
                    </li>
                    <li>
                        <x-sidebar-item icon="package" label="Архив" wireClick="goTo('archive')" :active="$section === 'archive'"
                            :count="$this->noteCounts->archiveCount" :isExpanded="$isExpanded" />
                    </li>
                    <li>
                        <x-sidebar-item icon="trash" label="Корзина" wireClick="goTo('trash')" :active="$section === 'trash'"
                            :count="$this->trashCount" :isExpanded="$isExpanded" />
                    </li>
                </ul>
            </li>
            <!-- Разделитель -->
            <li
                class="my-2 mx-4 h-px bg-gray-200 opacity-0 {{ $isExpanded ? 'opacity-100' : 'group-hover:opacity-100' }} transition-opacity duration-200">
            </li>
            <!-- Действия -->
            <li>
                <h4
                    class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap opacity-0 {{ $isExpanded ? 'opacity-100' : 'group-hover:opacity-100' }} transition-opacity duration-200">
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
            <li
                class="my-2 mx-4 h-px bg-gray-200 opacity-0 {{ $isExpanded ? 'opacity-100' : 'group-hover:opacity-100' }} transition-opacity duration-200">
            </li>
            <!-- Папки -->
            <li>
                <h4
                    class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap opacity-0 {{ $isExpanded ? 'opacity-100' : 'group-hover:opacity-100' }} transition-opacity duration-200">
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
            <li
                class="my-2 mx-4 h-px bg-gray-200 opacity-0 {{ $isExpanded ? 'opacity-100' : 'group-hover:opacity-100' }} transition-opacity duration-200">
            </li>
            <!-- Аккаунт -->
            <li>
                <h4
                    class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap opacity-0 {{ $isExpanded ? 'opacity-100' : 'group-hover:opacity-100' }} transition-opacity duration-200">
                    Аккаунт</h4>
                <ul class="mt-1 space-y-1">
                    <li>
                        <x-sidebar-item icon="user" label="Профиль" wireClick="goTo('profile')" :active="$section === 'profile'"
                            :isExpanded="$isExpanded" />
                    </li>
                    <li>
                        <x-sidebar-item icon="settings" label="Настройки" wireClick="goTo('setting')" :active="$section === 'setting'"
                            :isExpanded="$isExpanded" />
                    </li>
                    <li>
                        <x-sidebar-item icon="log-out" label="Выйти" wireClick="logout" :active="$section === 'logout'"
                            :isExpanded="$isExpanded" />
                    </li>
                </ul>
            </li>
        </ul>
    </nav>
</aside>

@script
    <script>
        let collapseTimer = null;
        const sidebar = document.getElementById('navigation-sidebar');
        let isHovered = false;

        function updateExpandedAttribute() {
            if (sidebar) {
                const hasFullWidth = sidebar.classList.contains('w-64');
                const shouldShowScrollbar = hasFullWidth || isHovered;
                sidebar.setAttribute('data-expanded', shouldShowScrollbar ? 'true' : 'false');
            }
        }

        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    updateExpandedAttribute();
                }
            });
        });

        if (sidebar) {
            observer.observe(sidebar, {
                attributes: true
            });
            updateExpandedAttribute();
        }

        sidebar?.addEventListener('mouseenter', () => {
            isHovered = true;
            if (collapseTimer) clearTimeout(collapseTimer);
            updateExpandedAttribute();
        });
        sidebar?.addEventListener('mouseleave', () => {
            isHovered = false;
            if (collapseTimer) clearTimeout(collapseTimer);
            updateExpandedAttribute();
            collapseTimer = setTimeout(() => {
                $wire.clearSidebarFlag();
            }, 200);
        });

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
                setTimeout(scrollToActiveItem, 100);
                return;
            }

            const activeLink = nav.querySelector('.bg-gradient-to-r.from-indigo-600.to-purple-600');
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
                setTimeout(setupScrollListenerAndRestore, 100);
                return;
            }
            const savedScroll = localStorage.getItem(STORAGE_KEY);
            if (savedScroll !== null) {
                nav.scrollTop = parseInt(savedScroll, 10);
            }
            nav.removeEventListener('scroll', handleScroll);
            nav.addEventListener('scroll', handleScroll);

            setTimeout(scrollToActiveItem, 100);
        }

        setupScrollListenerAndRestore();
    </script>
@endscript
