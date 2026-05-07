<!-- Боковое меню NavigationSidebar -->
<aside id="navigation-sidebar"
    class="group fixed left-0 top-0 h-full z-[9999] flex flex-col bg-white border-r border-gray-200 shadow-xl transition-all duration-300 ease-in-out w-[72px] hover:w-64 overflow-hidden"
    role="navigation" aria-label="Основное меню">
    <!-- Логотип -->
    <div class="flex items-center h-16 border-b border-gray-200 px-4 shrink-0">
        <div class="flex items-center justify-center w-10 h-10 flex-shrink-0">
            <x-logo></x-logo>
        </div>
        <h2
            class="ml-3 font-bold text-xl bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200">
            LeafNote</h2>
    </div>
    <!-- Навигация -->
    <nav id="sidebar-nav" class="flex-1 py-4 px-2 overflow-hidden group-hover:overflow-y-auto">
        <ul class="space-y-1">
            <!-- Основное меню -->
            <li>
                <h4
                    class="sidebar-section-title px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap overflow-hidden transition-all duration-200 py-0 max-h-0 group-hover:py-2 group-hover:max-h-10">
                    Основное меню</h4>
                <ul class="mt-1 space-y-1">
                    <li>
                        <x-sidebar-item icon="layout-grid" label="Главная доска" wireClick="goTo('dashboard-section')"
                            :active="$this->activeSection === 'dashboard-section'" :count="$this->noteCounts->dashboard" :isLoading="$isLoading && $loadingSection === 'dashboard-section'" />
                    </li>
                    <li>
                        <x-sidebar-item icon="star" label="Избранное" wireClick="goTo('favorite-section')"
                            :active="$this->activeSection === 'favorite-section'" :count="$this->noteCounts->favorite" :isLoading="$isLoading && $loadingSection === 'favorite-section'" />
                    </li>
                    <li>
                        <x-sidebar-item icon="lock" label="Сейф" wireClick="goTo('safe-section')" :active="$this->activeSection === 'safe-section'"
                            :count="$this->noteCounts->safe" :isLoading="$isLoading && $loadingSection === 'safe-section'" />
                    </li>
                    <li>
                        <x-sidebar-item icon="package" label="Архив" wireClick="goTo('archive-section')"
                            :active="$this->activeSection === 'archive-section'" :count="$this->noteCounts->archive" :isLoading="$isLoading && $loadingSection === 'archive-section'" />
                    </li>
                    <li>
                        <x-sidebar-item icon="trash" label="Корзина" wireClick="goTo('trash-section')"
                            :active="$this->activeSection === 'trash-section'" :count="$this->trashCount" :isLoading="$isLoading && $loadingSection === 'trash-section'" />
                    </li>
                </ul>
            </li>
            <!-- Разделитель -->
            <li class="my-2 mx-4 h-px bg-gray-200"></li>
            <!-- Действия -->
            <li>
                <h4
                    class="sidebar-section-title px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap overflow-hidden transition-all duration-200 py-0 max-h-0 group-hover:py-2 group-hover:max-h-10">
                    Действия</h4>
                <ul class="mt-1 space-y-1">
                    <li>
                        <x-sidebar-item icon="file-plus" label="Создать заметку" wireClick="goTo('create-note')"
                            :active="$this->activeSection === 'create-note'" :isLoading="$isLoading && $loadingSection === 'create-note'" />
                    </li>
                    <li>
                        <x-sidebar-item icon="list-plus" label="Создать список" wireClick="goTo('create-checklist')"
                            :active="$this->activeSection === 'create-checklist'" :isLoading="$isLoading && $loadingSection === 'create-checklist'" />
                    </li>
                    <li>
                        <x-sidebar-item icon="folder-plus" label="Создать папку" wireClick="goTo('create-folder')"
                            :active="$this->activeSection === 'create-folder'" :isLoading="$isLoading && $loadingSection === 'create-folder'" />
                    </li>
                </ul>
            </li>
            <!-- Разделитель -->
            <li class="my-2 mx-4 h-px bg-gray-200"></li>
            <!-- Папки -->
            <li>
                <h4
                    class="sidebar-section-title px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap overflow-hidden transition-all duration-200 py-0 max-h-0 group-hover:py-2 group-hover:max-h-10">
                    Папки</h4>
                <ul class="mt-1 space-y-1">
                    @foreach ($this->folders as $folder)
                        <li wire:key="folder-{{ $folder->id }}">
                            <x-sidebar-item icon="{{ $folder->icon }}" label="{{ $folder->title }}"
                                wireClick="goTo('folder-section', {{ $folder->id }})" :active="$this->activeSection === 'folder-section' && ($folderId ?? $previousFolderId) == $folder->id"
                                :count="$folder->notes_count" :isLoading="$isLoading && $loadingSection === 'folder-section' && $folderId == $folder->id" />
                        </li>
                    @endforeach
                </ul>
            </li>
            <!-- Разделитель -->
            <li class="my-2 mx-4 h-px bg-gray-200"></li>
            <!-- Аккаунт -->
            <li>
                <h4
                    class="sidebar-section-title px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap overflow-hidden transition-all duration-200 py-0 max-h-0 group-hover:py-2 group-hover:max-h-10">
                    Аккаунт</h4>
                <ul class="mt-1 space-y-1">
                    <li>
                        <x-sidebar-item icon="user" label="Профиль" wireClick="goTo('profile-section')"
                            :active="$this->activeSection === 'profile-section'" :isLoading="$isLoading && $loadingSection === 'profile-section'" />
                    </li>

                    <li>
                        <x-sidebar-item icon="log-out" label="Выйти" wireClick="confirmLogout" :active="$this->activeSection === 'logout'" />
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
