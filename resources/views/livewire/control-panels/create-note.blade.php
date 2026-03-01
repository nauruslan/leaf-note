<!-- CreateNote ControlPanel -->
<div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="bg-white rounded-xl shadow-md p-5 border border-gray-100">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">

            <!-- Left Block: Main Settings -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Folder Selection -->
                <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Папка:</span>
                    <div class="relative">
                        <select
                            class="appearance-none bg-gray-50 border border-gray-300 text-gray-700 py-2 pl-3 pr-8 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm min-w-[150px] hover:bg-gray-100 transition-colors">
                            <option value="">Выберите папку</option>
                            <option value="work">Работа</option>
                            <option value="personal">Личное</option>
                            <option value="ideas">Идеи</option>
                            <option value="archive">Архив</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                            <i data-lucide="chevron-down" class="w-3 h-3 text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <!-- Color Picker -->
                <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Цвет:</span>
                    <div class="flex items-center gap-1.5">
                        <button type="button"
                            class="w-6 h-6 rounded-full bg-white border-2 border-gray-300 hover:scale-110 transition-transform focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400"
                            title="Белый"></button>
                        <button type="button"
                            class="w-6 h-6 rounded-full bg-red-100 border-2 border-red-200 hover:scale-110 transition-transform focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-400"
                            title="Красный"></button>
                        <button type="button"
                            class="w-6 h-6 rounded-full bg-orange-100 border-2 border-orange-200 hover:scale-110 transition-transform focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-400"
                            title="Оранжевый"></button>
                        <button type="button"
                            class="w-6 h-6 rounded-full bg-amber-100 border-2 border-amber-200 hover:scale-110 transition-transform focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-400"
                            title="Желтый"></button>
                        <button type="button"
                            class="w-6 h-6 rounded-full bg-green-100 border-2 border-green-200 hover:scale-110 transition-transform focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-400"
                            title="Зеленый"></button>
                        <button type="button"
                            class="w-6 h-6 rounded-full bg-indigo-100 border-2 border-indigo-200 hover:scale-110 transition-transform focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-400"
                            title="Синий"></button>
                        <button type="button"
                            class="w-6 h-6 rounded-full bg-purple-100 border-2 border-purple-200 hover:scale-110 transition-transform focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-400"
                            title="Фиолетовый"></button>
                    </div>
                </div>
            </div>

            <!-- Right Block: Actions -->
            <div class="flex flex-wrap items-center gap-3 justify-end">
                <!-- Save Button -->
                <button type="submit"
                    class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium py-2.5 px-5 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Сохранить
                </button>

                <!-- Cancel Button -->
                <button type="button"
                    class="bg-gradient-to-r from-rose-500 to-red-600 hover:from-rose-600 hover:to-red-700 text-white font-medium py-2.5 px-5 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                    <i data-lucide="x" class="w-4 h-4"></i>
                    Отмена
                </button>
            </div>
        </div>
    </div>
</div>
