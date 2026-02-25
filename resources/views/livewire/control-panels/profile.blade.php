<!-- ControlPanel -->
<div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="bg-white rounded-xl shadow-md p-5">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <!-- Left Block: Create Buttons -->
            <div class="flex flex-wrap items-center gap-3">
                <button
                    class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium py-2.5 px-5 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Новая заметка
                </button>
                <button
                    class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2.5 px-5 rounded-lg shadow-sm hover:shadow transition-all flex items-center gap-2">
                    <i data-lucide="list" class="w-4 h-4"></i>
                    Новый список
                </button>
            </div>

            <!-- Right Block: Filters -->
            <div class="flex flex-wrap items-center gap-4 justify-end">
                <!-- Фильтр Dropdown -->
                <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Фильтр:</span>
                    <div class="relative">
                        <select
                            class="appearance-none bg-gray-100 border border-gray-300 text-gray-700 py-2 pl-3 pr-8 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm min-w-[100px]">
                            <option>Все</option>
                            <option>Заметки</option>
                            <option>Списки</option>
                            <option>Важные</option>
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
                            <option>По дате изменения</option>
                            <option>По дате создания</option>
                            <option>По названию</option>
                            <option>По приоритету</option>
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
