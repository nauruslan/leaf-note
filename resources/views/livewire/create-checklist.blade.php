<div>
    <!-- Header Section -->
    <header class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 ">
        <div class="bg-white rounded-b-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                        Создание списка
                    </h1>
                    <p class="text-sm text-gray-500 mt-0.5">Создайте новый список задач</p>
                </div>

                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" placeholder="Поиск шаблонов..."
                        class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 w-64 transition-all">
                </div>
            </div>
        </div>
    </header>

    <!-- ControlPanel Section -->
    <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="bg-white rounded-xl shadow-md p-5 border border-gray-100">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">

                <!-- Left Block: Main Settings -->
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Folder Selection -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Папка:</span>
                        <div class="relative">
                            <select wire:model.live="folderId"
                                class="appearance-none bg-gray-50 border border-gray-300 text-gray-700 py-2 pl-3 pr-8 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm min-w-[150px] hover:bg-gray-100 transition-colors">
                                <option value="">Выберите папку</option>
                                @foreach ($folders as $folder)
                                    <option value="{{ $folder->id }}">{{ $folder->title }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                                <i data-lucide="chevron-down" class="w-3 h-3 text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Favorite -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Избранное:</span>
                        <x-star :active="$is_favorite" wire:click="toggleFavorite" size="30px" />
                    </div>
                </div>

                <!-- Right Block: Actions -->
                <div class="flex flex-wrap items-center gap-3 justify-end">
                    <!-- Save Button -->
                    <button type="button" wire:click="prepareAndSave" wire:loading.attr="disabled"
                        class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium py-2.5 px-5 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Сохранить
                    </button>

                    <!-- Cancel Button -->
                    <button type="button" wire:click.prevent="cancel" wire:loading.attr="disabled"
                        class="bg-gradient-to-r from-rose-500 to-red-600 hover:from-rose-600 hover:to-red-700 text-white font-medium py-2.5 px-5 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                        <i data-lucide="x" class="w-4 h-4"></i>
                        Отмена
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Section -->
    <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 pb-6">
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden min-h-[600px] flex flex-col">
            <!-- Note Title Input -->
            <div class="px-6 pt-6 pb-2 border-b border-gray-100">
                <input type="text" wire:model="title" placeholder="Название списка"
                    class="p-0 w-full text-2xl font-bold text-gray-900 placeholder-gray-400 border-none focus:outline-none focus:ring-0 bg-transparent">
            </div>
            <!-- Checklist Editor & Footer -->
            <div class="flex flex-col flex-grow">
                <div wire:ignore class="flex-grow p-6 flex flex-col items-center justify-center h-full">
                    <div id="create-checklist-editor"
                        class="checklist-editor prose prose-indigo max-w-none focus:outline-none min-h-[400px] text-gray-700 w-full h-full flex flex-col items-center justify-center">
                    </div>
                </div>

                <!-- Footer Info (ignored) -->
                <div wire:ignore class="mt-auto">
                    <div
                        class="px-6 py-3 border-t border-gray-200 bg-gray-50/50 flex justify-between items-center text-xs text-gray-500">
                        <div class="flex items-center gap-4">
                            <span>Создано: только что</span>
                            <span>•</span>
                            <span data-task-count>0 задач</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Link Modal (ignored) -->
        <div wire:ignore>
            <div id="link-modal" class="link-modal">
                <div class="link-modal-content">
                    <h3 class="link-modal-title">Введите ссылку</h3>
                    <input type="url" id="link-input" class="link-modal-input" placeholder="https://example.com"
                        autocomplete="off">
                    <div class="link-modal-buttons">
                        <button type="button" class="link-modal-btn link-modal-btn-ok" data-link-action="ok">
                            ОК
                        </button>
                        <button type="button" class="link-modal-btn link-modal-btn-cancel" data-link-action="cancel">
                            Отменить
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
