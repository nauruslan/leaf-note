<div>
    <!-- Header Section -->
    <header class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 ">
        <div class="bg-white rounded-b-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                        Настройки
                    </h1>
                    <p class="text-sm text-gray-500 mt-0.5">Управление настройками приложения</p>
                </div>

                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" placeholder="Поиск настроек..."
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
                    <button wire:click="saveSettings"
                        class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium py-2.5 px-5 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Сохранить настройки
                    </button>
                    <button wire:click="resetSettings"
                        class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2.5 px-5 rounded-lg shadow-sm hover:shadow transition-all flex items-center gap-2">
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                        Сбросить настройки
                    </button>
                </div>

                <!-- Right Block: Options -->
                <div class="flex flex-wrap items-center gap-4 justify-end">
                    <!-- Версия -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Версия:</span>
                        <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-sm">1.2.3</span>
                    </div>

                    <!-- Режим -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Режим:</span>
                        <div class="relative">
                            <select wire:model="mode"
                                class="appearance-none bg-gray-100 border border-gray-300 text-gray-700 py-2 pl-3 pr-8 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm min-w-[120px]">
                                <option value="user">Пользователь</option>
                                <option value="admin">Администратор</option>
                                <option value="developer">Разработчик</option>
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
            <h3 class="text-xl font-bold mb-4">Настройки приложения</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Левая колонка -->
                <div class="space-y-6">
                    <!-- Общие настройки -->
                    <div>
                        <h4 class="font-bold text-lg mb-3">Общие</h4>
                        <div class="space-y-4">
                            <label class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Тёмная тема</span>
                                <input type="checkbox" wire:model="darkMode" class="toggle">
                            </label>
                            <label class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Автосохранение</span>
                                <input type="checkbox" wire:model="autoSave" class="toggle" checked>
                            </label>
                            <label class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Уведомления</span>
                                <input type="checkbox" wire:model="notifications" class="toggle" checked>
                            </label>
                        </div>
                    </div>

                    <!-- Безопасность -->
                    <div>
                        <h4 class="font-bold text-lg mb-3">Безопасность</h4>
                        <div class="space-y-4">
                            <label class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Двухфакторная аутентификация</span>
                                <input type="checkbox" wire:model="twoFactor" class="toggle">
                            </label>
                            <label class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Автоматический выход</span>
                                <input type="checkbox" wire:model="autoLogout" class="toggle">
                            </label>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Таймаут сессии (мин)</label>
                                <input type="number" wire:model="sessionTimeout" min="1" max="480"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Правая колонка -->
                <div class="space-y-6">
                    <!-- Уведомления -->
                    <div>
                        <h4 class="font-bold text-lg mb-3">Уведомления</h4>
                        <div class="space-y-4">
                            <label class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Email уведомления</span>
                                <input type="checkbox" wire:model="emailNotifications" class="toggle" checked>
                            </label>
                            <label class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Push-уведомления</span>
                                <input type="checkbox" wire:model="pushNotifications" class="toggle">
                            </label>
                            <label class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Еженедельный отчёт</span>
                                <input type="checkbox" wire:model="weeklyReport" class="toggle" checked>
                            </label>
                        </div>
                    </div>

                    <!-- Экспорт данных -->
                    <div>
                        <h4 class="font-bold text-lg mb-3">Данные</h4>
                        <div class="space-y-4">
                            <button
                                class="w-full bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2.5 px-5 rounded-lg shadow-sm hover:shadow transition-all flex items-center justify-center gap-2">
                                <i data-lucide="download" class="w-4 h-4"></i>
                                Экспорт всех данных
                            </button>
                            <button
                                class="w-full bg-white border border-red-300 hover:bg-red-50 text-red-700 font-medium py-2.5 px-5 rounded-lg shadow-sm hover:shadow transition-all flex items-center justify-center gap-2">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                Удалить все данныеdiv>
                        </div>

                        <!-- О приложении -->
                        <div>
                            <h4 class="font-bold text-lg mb-3">О приложении</h4>
                            <div class="space-y-2 text-sm text-gray-600">
                                <p><strong>LeafNote</strong> — приложение для управления заметками и списками.</p>
                                <p>Версия: 1.2.3 (Build 2026)</p>
                                <p>Лицензия: MIT</p>
                                <p>Разработчик: Команда LeafNote</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
