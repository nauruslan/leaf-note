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
                        <a href="#" wire:click.prevent="navigateTo('dashboard')"
                            class="flex items-center w-full px-4 py-3 rounded-lg text-gray-700  transition-all group/item {{ $section === 'dashboard' ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white' : 'hover:bg-gray-100 hover:text-indigo-600' }}">
                            <i data-lucide="layout-grid" class="w-6 h-6 flex-shrink-0"></i>
                            <span
                                class="ml-3 font-medium whitespace-nowrap opacity-0 {{ $isExpanded ? 'opacity-100' : 'group-hover:opacity-100' }} transition-opacity duration-200">Главная
                                доска</span>
                            <span
                                class="ml-auto bg-indigo-100 text-indigo-700 text-xs font-medium px-2 py-0.5 rounded-full opacity-0 {{ $isExpanded ? 'opacity-100' : 'group-hover:opacity-100' }} transition-opacity">
                                {{ $this->dashboardCount }}
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="#" wire:click.prevent="navigateTo('checklist')"
                            class="flex items-center w-full px-4 py-3 rounded-lg text-gray-700  transition-all group/item {{ $section === 'checklist' ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white' : 'hover:bg-gray-100 hover:text-indigo-600' }}">
                            <i data-lucide="clipboard-list" class="w-6 h-6 flex-shrink-0"></i>
                            <span
                                class="ml-3 font-medium whitespace-nowrap opacity-0 {{ $isExpanded ? 'opacity-100' : 'group-hover:opacity-100' }} transition-opacity duration-200">Списки
                                задач</span>
                            <span
                                class="ml-auto bg-indigo-100 text-indigo-700 text-xs font-medium px-2 py-0.5 rounded-full opacity-0 {{ $isExpanded ? 'opacity-100' : 'group-hover:opacity-100' }} transition-opacity">
                                {{ $this->checklistCount }}
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="#" wire:click.prevent="navigateTo('favorite')"
                            class="flex items-center w-full px-4 py-3 rounded-lg text-gray-700  transition-all group/item {{ $section === 'favorite' ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white' : 'hover:bg-gray-100 hover:text-indigo-600' }}">
                            <i data-lucide="star" class="w-6 h-6 flex-shrink-0"></i>
                            <span
                                class="ml-3 font-medium whitespace-nowrap opacity-0 {{ $isExpanded ? 'opacity-100' : 'group-hover:opacity-100' }} transition-opacity duration-200">Избранное</span>
                            <span
                                class="ml-auto bg-indigo-100 text-indigo-700 text-xs font-medium px-2 py-0.5 rounded-full opacity-0 {{ $isExpanded ? 'opacity-100' : 'group-hover:opacity-100' }} transition-opacity">
                                {{ $this->favoriteCount }}
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="#" wire:click.prevent="navigateTo('safe')"
                            class="flex items-center w-full px-4 py-3 rounded-lg text-gray-700 transition-all group/item {{ $section === 'safe' ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white' : 'hover:bg-gray-100 hover:text-indigo-600' }}">
                            <i data-lucide="lock" class="w-6 h-6 flex-shrink-0"></i>
                            <span
                                class="ml-3 font-medium whitespace-nowrap opacity-0 {{ $isExpanded ? 'opacity-100' : 'group-hover:opacity-100' }} transition-opacity duration-200">Сейф</span>
                            <span
                                class="ml-auto bg-indigo-100 text-indigo-700 text-xs font-medium px-2 py-0.5 rounded-full opacity-0 {{ $isExpanded ? 'opacity-100' : 'group-hover:opacity-100' }} transition-opacity">
                                {{ $this->safeCount }}
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="#" wire:click.prevent="navigateTo('archive')"
                            class="flex items-center w-full px-4 py-3 rounded-lg text-gray-700 transition-all group/item {{ $section === 'archive' ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white' : 'hover:bg-gray-100 hover:text-indigo-600' }}">
                            <i data-lucide="package" class="w-6 h-6 flex-shrink-0"></i>
                            <span
                                class="ml-3 font-medium whitespace-nowrap opacity-0 {{ $isExpanded ? 'opacity-100' : 'group-hover:opacity-100' }} transition-opacity duration-200">Архив</span>
                            <span
                                class="ml-auto bg-indigo-100 text-indigo-700 text-xs font-medium px-2 py-0.5 rounded-full opacity-0 {{ $isExpanded ? 'opacity-100' : 'group-hover:opacity-100' }} transition-opacity">
                                {{ $this->archiveCount }}
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="#" wire:click.prevent="navigateTo('trash')"
                            class="flex items-center w-full px-4 py-3 rounded-lg text-gray-700 transition-all group/item {{ $section === 'trash' ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white' : 'hover:bg-gray-100 hover:text-indigo-600' }}">
                            <i data-lucide="trash" class="w-6 h-6 flex-shrink-0"></i>
                            <span
                                class="ml-3 font-medium whitespace-nowrap opacity-0 {{ $isExpanded ? 'opacity-100' : 'group-hover:opacity-100' }} transition-opacity duration-200">Корзина</span>
                            <span
                                class="ml-auto bg-indigo-100 text-indigo-700 text-xs font-medium px-2 py-0.5 rounded-full opacity-0 {{ $isExpanded ? 'opacity-100' : 'group-hover:opacity-100' }} transition-opacity">
                                {{ $this->trashCount }}
                            </span>
                        </a>
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
                        <a href="#" wire:click.prevent="navigateTo('create-note')"
                            class="flex items-center w-full px-4 py-3 rounded-lg text-gray-700 transition-all group/item {{ $section === 'create-note' ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white' : 'hover:bg-gray-100 hover:text-indigo-600' }}">
                            <i data-lucide="file-plus" class="w-6 h-6 flex-shrink-0"></i>
                            <span
                                class="ml-3 font-medium whitespace-nowrap opacity-0 {{ $isExpanded ? 'opacity-100' : 'group-hover:opacity-100' }} transition-opacity duration-200">Создать
                                заметку</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" wire:click.prevent="navigateTo('create-checklist')"
                            class="flex items-center w-full px-4 py-3 rounded-lg text-gray-700 transition-all group/item {{ $section === 'create-checklist' ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white' : 'hover:bg-gray-100 hover:text-indigo-600' }}">
                            <i data-lucide="list-plus" class="w-6 h-6 flex-shrink-0"></i>
                            <span
                                class="ml-3 font-medium whitespace-nowrap opacity-0 {{ $isExpanded ? 'opacity-100' : 'group-hover:opacity-100' }} transition-opacity duration-200">Создать
                                список</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" wire:click.prevent="navigateTo('create-folder')"
                            class="flex items-center w-full px-4 py-3 rounded-lg text-gray-700 transition-all group/item {{ $section === 'create-folder' ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white' : 'hover:bg-gray-100 hover:text-indigo-600' }}">
                            <i data-lucide="folder-plus" class="w-6 h-6 flex-shrink-0"></i>
                            <span
                                class="ml-3 font-medium whitespace-nowrap opacity-0 {{ $isExpanded ? 'opacity-100' : 'group-hover:opacity-100' }} transition-opacity duration-200">Создать
                                папку</span>
                        </a>
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
                    @foreach ($folders as $folder)
                        <li wire:key="folder-{{ $folder->id }}">
                            <a href="#" wire:click.prevent="navigateTo('folder', {{ $folder->id }})"
                                class="flex items-center w-full px-4 py-3 rounded-lg text-gray-700transition-all group/item {{ $section === 'folder' && $folderId == $folder->id ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white' : 'hover:bg-gray-100 hover:text-indigo-600' }}">
                                <i data-lucide="{{ $folder->icon }}" class="w-6 h-6 flex-shrink-0 "></i>
                                <span
                                    class="ml-3 font-medium whitespace-nowrap opacity-0 {{ $isExpanded ? 'opacity-100' : 'group-hover:opacity-100' }} transition-opacity duration-200">
                                    {{ $folder->title }}
                                </span>
                                <span
                                    class="ml-auto bg-indigo-100 text-indigo-700 text-xs font-medium px-2 py-0.5 rounded-full opacity-0 {{ $isExpanded ? 'opacity-100' : 'group-hover:opacity-100' }} transition-opacity">
                                    {{ $folder->notes_count }}
                                </span>

                            </a>
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
                        <a href="#" wire:click.prevent="navigateTo('profile')"
                            class="flex items-center w-full px-4 py-3 rounded-lg text-gray-700 transition-all group/item {{ $section === 'profile' ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white' : 'hover:bg-gray-100 hover:text-indigo-600' }}">
                            <i data-lucide="user" class="w-6 h-6 flex-shrink-0"></i>
                            <span
                                class="ml-3 font-medium whitespace-nowrap opacity-0 {{ $isExpanded ? 'opacity-100' : 'group-hover:opacity-100' }} transition-opacity duration-200">Профиль</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" wire:click.prevent="navigateTo('setting')"
                            class="flex items-center w-full px-4 py-3 rounded-lg text-gray-700 transition-all group/item {{ $section === 'setting' ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white' : 'hover:bg-gray-100 hover:text-indigo-600' }}">
                            <i data-lucide="settings" class="w-6 h-6 flex-shrink-0"></i>
                            <span
                                class="ml-3 font-medium whitespace-nowrap opacity-0 {{ $isExpanded ? 'opacity-100' : 'group-hover:opacity-100' }} transition-opacity duration-200">Настройки</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" wire:click.prevent="logout"
                            class="flex items-center w-full px-4 py-3 rounded-lg text-gray-700 transition-all group/item {{ $section === 'logout' ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white' : 'hover:bg-gray-100 hover:text-indigo-600' }}">
                            <i data-lucide="log-out" class="w-6 h-6 flex-shrink-0"></i>
                            <span
                                class="ml-3 font-medium whitespace-nowrap opacity-0 {{ $isExpanded ? 'opacity-100' : 'group-hover:opacity-100' }} transition-opacity duration-200">Выйти</span>
                        </a>
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
            }, 150);
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
        }

        setupScrollListenerAndRestore();
    </script>
@endscript
