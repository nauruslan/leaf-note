<div>
    <!-- Header Section -->
    <header class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 ">
        <div class="bg-white rounded-b-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                        Создание папки
                    </h1>
                    <p class="text-sm text-gray-500 mt-0.5">Создайте новую папку для организации заметок</p>
                </div>

                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" placeholder="Поиск папок..."
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
                    <button wire:click="createFolder"
                        class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium py-2.5 px-5 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                        <i data-lucide="folder-plus" class="w-4 h-4"></i>
                        Создать папку
                    </button>
                    <button wire:click="cancel"
                        class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2.5 px-5 rounded-lg shadow-sm hover:shadow transition-all flex items-center gap-2">
                        <i data-lucide="x" class="w-4 h-4"></i>
                        Отмена
                    </button>
                </div>

                <!-- Right Block: Options -->
                <div class="flex flex-wrap items-center gap-4 justify-end">
                    <!-- Видимость -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Видимость:</span>
                        <div class="relative">
                            <select
                                class="appearance-none bg-gray-100 border border-gray-300 text-gray-700 py-2 pl-3 pr-8 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm min-w-[120px]">
                                <option value="private">Личная</option>
                                <option value="shared">Общая</option>
                                <option value="public">Публичная</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                                <i data-lucide="chevron-down" class="w-3 h-3 text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Цвет -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Цвет:</span>
                        <div class="flex items-center gap-1">
                            <button class="w-6 h-6 rounded-full bg-indigo-500 border-2 border-white shadow"></button>
                            <button class="w-6 h-6 rounded-full bg-green-500 border-2 border-white shadow"></button>
                            <button class="w-6 h-6 rounded-full bg-yellow-500 border-2 border-white shadow"></button>
                            <button class="w-6 h-6 rounded-full bg-red-500 border-2 border-white shadow"></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Section -->
    <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 mb-6">
        <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-xl font-bold mb-4">Свойства папки</h3>
            <div class="space-y-6">
                <!-- Название папки -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Название папки</label>
                    <input type="text" wire:model="name"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Введите название папки">
                </div>
                <!-- Описание -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Описание</label>
                    <textarea rows="3" wire:model="description"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Введите описание папки..."></textarea>
                </div>
                <!-- Теги -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Тегиlabel>
                        <div class="flex flex-wrap gap-2">
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-indigo-100 text-indigo-800">
                                Работа
                                <button class="ml-1 text-indigo-600 hover:text-indigo-900">
                                    <i data-lucide="x" class="w-3 h-3"></i>
                                </button>
                            </span>
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-green-100 text-green-800">
                                Личное
                                <button class="ml-1 text-green-600 hover:text-green-900">
                                    <i data-lucide="x" class="w-3 h-3"></i>
                                </button>
                            </span>
                            <button class="text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                                <i data-lucide="plus" class="w-4 h-4"></i>
                                Добавить тег
                            </button>
                        </div>
                </div>
                <!-- Иконка -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Иконка папки</label>
                    <div class="flex items-center gap-4">
                        <div class="flex items-center justify-center w-12 h-12 bg-gray-100 rounded-lg">
                            <i data-lucide="folder" class="w-6 h-6 text-gray-600"></i>
                        </div>
                        <button class="text-sm text-indigo-600 hover:text-indigo-800">Выбрать другую иконку</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
