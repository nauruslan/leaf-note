<div>
    <!-- Header Section -->
    <header class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 ">
        <div class="bg-white rounded-b-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                        Избранное
                    </h1>
                    <p class="text-sm text-gray-500 mt-0.5">Ваши избранные заметки и списки</p>
                </div>

                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" wire:model="search" placeholder="Поиск в избранном..."
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
                    <button wire:click="$dispatch('openModal', 'add-to-favorites')"
                        class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium py-2.5 px-5 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                        <i data-lucide="star" class="w-4 h-4"></i>
                        Добавить в избранное
                    </button>
                    <button wire:click="exportFavorites"
                        class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2.5 px-5 rounded-lg shadow-sm hover:shadow transition-all flex items-center gap-2">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        Экспорт избранного
                    </button>
                </div>

                <!-- Right Block: Filters -->
                <div class="flex flex-wrap items-center gap-4 justify-end">
                    <!-- Тип -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Тип:</span>
                        <div class="relative">
                            <select
                                class="appearance-none bg-gray-100 border border-gray-300 text-gray-700 py-2 pl-3 pr-8 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm min-w-[120px]">
                                <option value="all">Все</option>
                                <option value="notes">Заметки</option>
                                <option value="checklists">Списки</option>
                                <option value="folders">Папки</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                                <i data-lucide="chevron-down" class="w-3 h-3 text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Сортировка -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Сортировка:</span>
                        <div class="relative">
                            <select
                                class="appearance-none bg-gray-100 border border-gray-300 text-gray-700 py-2 pl-3 pr-8 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm min-w-[120px]">
                                <option value="recent">Недавние</option>
                                <option value="oldest">Старые</option>
                                <option value="name">По названию</option>
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
            <h3 class="text-xl font-bold mb-4">Избранные элементы</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Пример избранной заметки -->
                <div class="border border-gray-200 rounded-xl p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between">
                        <div>
                            <h4 class="font-bold text-lg">Идеи для проекта</h4>
                            <p class="text-sm text-gray-500 mt-1">Заметка • 2 дня назад</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button wire:click="toggleFavorite(1)" class="text-yellow-500 hover:text-yellow-700">
                                <i data-lucide="star" class="w-5 h-5 fill-current"></i>
                            </button>
                            <button wire:click="removeFromFavorites(1)" class="text-red-500 hover:text-red-700">
                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-gray-600 line-clamp-2">Собрал идеи для нового проекта по автоматизации
                            процессов. Нужно обсудить с командой.</p>
                    </div>
                    <div class="mt-4 flex items-center justify-between text-sm">
                        <span class="text-gray-500">Папка: <strong>Работа</strong></span>
                        <span class="px-2 py-1 bg-indigo-100 text-indigo-800 rounded-full text-xs">Заметка</span>
                    </div>
                </div>

                <!-- Пример избранного списка -->
                <div class="border border-gray-200 rounded-xl p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between">
                        <div>
                            <h4 class="font-bold text-lg">Еженедельные задачи</h4>
                            <p class="text-sm text-gray-500 mt-1">Список • 5 дней назад</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button wire:click="toggleFavorite(2)" class="text-yellow-500 hover:text-yellow-700">
                                <i data-lucide="star" class="w-5 h-5 fill-current"></i>
                            </button>
                            <button wire:click="removeFromFavorites(2)" class="text-red-500 hover:text-red-700">
                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-gray-600 line-clamp-2">Список задач на неделю с приоритетами и сроками.
                        </p>
                    </div>
                    <div class="mt-4 flex items-center justify-between text-sm">
                        <span class="text-gray-500">Прогресс: <strong>60%</strong></span>
                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Список</span>
                    </div>
                </div>

                <!-- Пример избранной папки -->
                <div class="border border-gray-200 rounded-xl p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between">
                        <div>
                            <h4 class="font-bold text-lg">Личные заметки</h4>
                            <p class="text-sm text-gray-500 mt-1">Папка • 1 неделю назад</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button wire:click="toggleFavorite(3)" class="text-yellow-500 hover:text-yellow-700">
                                <i data-lucide="star" class="w-5 h-5 fill-current"></i>
                            </button>
                            <button wire:click="removeFromFavorites(3)" class="text-red-500 hover:text-red-700">
                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-gray-600 line-clamp-2">Папка с личными заметками, идеями и мыслями.</p>
                    </div>
                    <div class="mt-4 flex items-center justify-between text-sm">
                        <span class="text-gray-500">Элементов: <strong>12</strong></span>
                        <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs">Папка</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
