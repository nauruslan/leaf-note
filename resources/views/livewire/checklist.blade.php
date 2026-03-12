<div>
    <!-- Header Section -->
    <header class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 ">
        <div class="bg-white rounded-b-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                        Списки задач
                    </h1>
                    <p class="text-sm text-gray-500 mt-0.5">Управление вашими списками задач</p>
                </div>

                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" wire:model="search" placeholder="Поиск списков..."
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
                    <button wire:click="$dispatch('openModal', 'create-checklist')"
                        class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium py-2.5 px-5 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        Новый список
                    </button>
                    <button wire:click="export"
                        class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2.5 px-5 rounded-lg shadow-sm hover:shadow transition-all flex items-center gap-2">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        Экспорт
                    </button>
                    <button wire:click="import"
                        class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2.5 px-5 rounded-lg shadow-sm hover:shadow transition-all flex items-center gap-2">
                        <i data-lucide="upload" class="w-4 h-4"></i>
                        Импорт
                    </button>
                </div>

                <!-- Right Block: Filters -->
                <div class="flex flex-wrap items-center gap-4 justify-end">
                    <!-- Сортировка -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Сортировка:</span>
                        <div class="relative">
                            <select
                                class="appearance-none bg-gray-100 border border-gray-300 text-gray-700 py-2 pl-3 pr-8 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm min-w-[120px]">
                                <option value="newest">Сначала новые</option>
                                <option value="oldest">Сначала старые</option>
                                <option value="name">По названию</option>
                                <option value="priority">По приоритету</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                                <i data-lucide="chevron-down" class="w-3 h-3 text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Фильтр по статусу -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Статус:</span>
                        <div class="relative">
                            <select
                                class="appearance-none bg-gray-100 border border-gray-300 text-gray-700 py-2 pl-3 pr-8 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm min-w-[120px]">
                                <option value="all">Все</option>
                                <option value="active">Активные</option>
                                <option value="completed">Завершённые</option>
                                <option value="archived">В архиве</option>
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
            <h3 class="text-xl font-bold mb-4">Ваши списки</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Пример списка -->
                <div class="border border-gray-200 rounded-xl p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between">
                        <div>
                            <h4 class="font-bold text-lg">Рабочие задачи</h4>
                            <p class="text-sm text-gray-500 mt-1">5 задач, 2 завершены</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button wire:click="toggleComplete(1)" class="text-green-600 hover:text-green-800">
                                <i data-lucide="check-circle" class="w-5 h-5"></i>
                            </button>
                            <button wire:click="deleteChecklist(1)" class="text-red-500 hover:text-red-700">
                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Приоритет:</span>
                            <span
                                class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium">Средний</span>
                        </div>
                        <div class="mt-3">
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-indigo-600 h-2 rounded-full" style="width: 40%"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Прогресс: 40%</p>
                        </div>
                    </div>
                </div>

                <!-- Второй список -->
                <div class="border border-gray-200 rounded-xl p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between">
                        <div>
                            <h4 class="font-bold text-lg">Покупки</h4>
                            <p class="text-sm text-gray-500 mt-1">8 задач, 5 завершены</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button wire:click="toggleComplete(2)" class="text-green-600 hover:text-green-800">
                                <i data-lucide="check-circle" class="w-5 h-5"></i>
                            </button>
                            <button wire:click="deleteChecklist(2)" class="text-red-500 hover:text-red-700">
                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Приоритет:</span>
                            <span
                                class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">Низкий</span>
                        </div>
                        <div class="mt-3">
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-600 h-2 rounded-full" style="width: 62%"></div>
                            </div>
                            <p class="text-xs text- mt-1">Прогресс: 62%</p>
                        </div>
                    </div>
                </div>

                <!-- Третий список -->
                <div class="border border-gray-200 rounded-xl p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between">
                        <div>
                            <h4 class="font-bold text-lg">Личные цели</h4>
                            <p class="text-sm text-gray-500 mt-1">3 задачи, 0 завершены</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button wire:click="toggleComplete(3)" class="text-green-600 hover:text-green-800">
                                <i data-lucide="check-circle" class="w-5 h-5"></i>
                            </button>
                            <button wire:click="deleteChecklist(3)" class="text-red-500 hover:text-red-700">
                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Приоритет:</span>
                            <span
                                class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium">Высокий</span>
                        </div>
                        <div class="mt-3">
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-red-600 h-2 rounded-full" style="width: 10%"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Прогресс: 10%</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
