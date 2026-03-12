<div>
    <!-- Header Section -->
    <header class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 ">
        <div class="bg-white rounded-b-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                        Профиль пользователя
                    </h1>
                    <p class="text-sm text-gray-500 mt-0.5">Управление вашими настройками и данными</p>
                </div>

                <div class="relative">
                    <div class="absolute-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" placeholder="Поиск в профиле..."
                        class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 w-64 transition-all">
                </div>
            </div>
        </div>
    </header>

    <!-- ControlPanel Section -->
    <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="bg-white rounded-xl shadow-md p-5">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <!-- Left Block: Actions -->
                <div class="flex flex-wrap items-center gap-3">
                    <button wire:click="saveProfile"
                        class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium py-2.5 px-5 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Сохранить изменения
                    </button>
                    <button wire:click="changePassword"
                        class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2.5 px-5 rounded-lg shadow-sm hover:shadow transition-all flex items-center gap-2">
                        <i data-lucide="key" class="w-4 h-4"></i>
                        Сменить пароль
                    </button>
                    <button wire:click="$dispatch('navigate', {section: 'dashboard'})"
                        class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2.5 px-5 rounded-lg shadow-sm hover:shadow transition-all flex items-center gap-2">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        Назад
                    </button>
                </div>

                <!-- Right Block: Options -->
                <div class="flex flex-wrap items-center gap-4 justify-end">
                    <!-- Язык -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Язык:</span>
                        <div class="relative">
                            <select
                                class="appearance-none bg-gray-100 border border-gray-300 text-gray-700 py-2 pl-3 pr-8 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm min-w-[120px]">
                                <option value="ru">Русский</option>
                                <option value="en">English</option>
                                <option value="de">Deutsch</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                                <i data-lucide="chevron-down" class="w-3 h-3 text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Тема -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Тема:</span>
                        <div class="relative">
                            <select
                                class="appearance-none bg-gray-100 border border-gray-300 text-gray-700 py-2 pl-3 pr-8 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm min-w-[120px]">
                                <option value="light">Светлая</option>
                                <option value="dark">Тёмная</option>
                                <option value="auto">Авто</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                                <i data-lucide="chevron-down" class="w-3 h-3 text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Section -->
    <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 mb-6">
        <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-xl font-bold mb-4">Личные данные</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Левая колонка -->
                <div class="space-y-6">
                    <!-- Аватар -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Аватар</label>
                        <div class="flex items-center gap-4">
                            <div
                                class="w-20 h-20 rounded-full bg-gradient-to-r from-indigo-400 to-purple-500 flex items-center justify-center text-white text-2xl font-bold">
                                U
                            </div>
                            <div>
                                <button class="text-sm text-indigo-600 hover:text-indigo-800">Загрузить новое
                                    фото</button>
                                <p class="text-xs text-gray-500 mt-1">JPG, PNG до 2 МБ</p>
                            </div>
                        </div>
                    </div>
                    <!-- Имя -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Имя</label>
                        <input type="text" wire:model="firstName"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Введите имя">
                    </div>
                    <!-- Фамилия -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Фамилия</label>
                        <input type="text" wire:model="lastName"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Введите фамилию">
                    </div>
                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" wire:model="email"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="email@example.com">
                    </div>
                </div>

                <!-- Правая колонка -->
                <div class="space-y-6">
                    <!-- О себе -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">О себе</label>
                        <textarea rows="4" wire:model="bio"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Расскажите о себе..."></textarea>
                    </div>
                    <!-- Уведомления -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Уведомления</label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" wire:model="notifyEmail" class="rounded text-indigo-600">
                                <span class="ml-2 text-sm">Email уведомления</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" wire:model="notifyPush" class="rounded text-indigo-600">
                                <span class="ml-2 text-sm">Push-уведомления</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" wire:model="notifyWeekly" class="rounded text-indigo-600">
                                <span class="ml-2 text-sm">Еженедельный отчёт</span>
                            </label>
                        </div>
                    </div>
                    <!-- Статистика -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Статистика</label>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-sm text-gray-500">Заметок</p>
                                <p class="text-2xl font-bold">42</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-sm text-gray-500">Списков</p>
                                <p class="text-2xl font-bold">7</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
