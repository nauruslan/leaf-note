<div>
    <!-- Unified Safe Component (Header + ControlPanel + Content) -->
    <!-- Header Section -->
    <header class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 ">
        <div class="bg-white rounded-b-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                        Сейф
                    </h1>
                    <p class="text-sm text-gray-500 mt-0.5">Защищённые заметки с шифрованием</p>
                </div>

                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" placeholder="Поиск в сейфе..."
                        class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 w-64 transition-all">
                </div>
            </div>
        </div>
    </header>

    <!-- ControlPanel Section -->
    <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="bg-white rounded-xl shadow-md p-5">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <!-- Left Block: Create Buttons -->
                <div class="flex flex-wrap items-center gap-3">
                    <button wire:click="createSafeNote"
                        class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium py-2.5 px-5 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                        Новая защищённаяка
                        button>
                </div>

                <!-- Right Block: Filters -->
                <div class="flex flex-wrap items-center gap-4 justify-end">
                    <!-- Фильтр Dropdown -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Фильтр:</span>
                        <div class="relative">
                            <select
                                class="appearance-none bg-gray-100 border border-gray-300 text-gray-700 py-2 pl-3 pr-8 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm min-w-[100px]">
                                <option value="all">Все</option>
                                <option value="notes">Заметки</option>
                                <option value="checklists">Списки</option>
                                <option value="important">Важные</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                                <i data-lucide="chevron-down" class="w-3 h-3 text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Сортировка Dropdown -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Сортировка:</span>
                        <div class="relative">
                            <select
                                class="appearance-none bg-gray-100 border border-gray-300 text-gray-700 py-2 pl-3 pr-8 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm min-w-[140px]">
                                <option value="updated">По дате изменения</option>
                                <option value="created">По дате создания</option>
                                <option value="title">По названию</option>
                                <option value="priority">По приоритету</option>
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
    <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 mb-6 flex flex-wrap gap-5">
        <!-- Placeholder for safe items -->
        <div
            class="min-w-[320px] basis-[320px] h-[340px] flex grow flex-col p-4 bg-white rounded-xl shadow-md border border-gray-200 hover:shadow-lg transition-all">
            <div class="flex items-start justify-between mb-6">
                <div class="w-8 flex-shrink-0 self-stretch">
                    <i data-lucide="lock" class="w-8 h-8 text-gray-500"></i>
                </div>
                <div class="flex flex-1 ml-3 flex-col">
                    <h3 class="font-bold text-lg text-gray-900">Защищённая заметка</h3>
                    <p class="text-xs text-gray-500">Создано: 3 марта 2026</p>
                </div>
                <div class="self-stretch flex flex-end items-baseline">
                    <button class="text-gray-400 hover:text-yellow-400 p-1 mb-1" aria-label="Добавить в избранное">
                        <i data-lucide="star" class="w-6 h-6"></i>
                    </button>
                    <button class="text-gray-400 hover:text-gray-600 p-1">
                        <i data-lucide="more-vertical" class="w-6 h-6"></i>
                    </button>
                </div>
            </div>
            <div class="flex-grow flex">
                <p class="text-gray-600">Эта заметка защищена шифрованием. Для просмотра требуется ввести пароль.</p>
            </div>
            <div class="flex justify-between border-t border-gray-200 pt-5 mt-auto">
                <div
                    class="px-3 py-1.5 rounded-lg text-md font-bold bg-green-100 text-green-800 flex items-center gap-1.5">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                    <span>Сейф</span>
                </div>
                <button
                    class="text-indigo-600 hover:text-indigo-800 font-bold text-md flex items-center gap-1.5 px-3 py-1.5 rounded-lg">
                    <span>Открыть</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </div>
        </div>
    </div>
</div>
