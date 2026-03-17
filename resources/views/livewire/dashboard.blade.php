<div>
    <!-- Header Section -->
    <header class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 ">
        <div class="bg-white rounded-b-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                        Главная доска
                    </h1>
                    <p class="text-sm text-gray-500 mt-0.5">Все ваши заметки и списки в одном месте</p>
                </div>

                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" placeholder="Поиск заметок..."
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
                    <button wire:click="createNote"
                        class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium py-2.5 px-5 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        Новая заметка
                    </button>
                    <button wire:click="createChecklist"
                        class="bg-white border border-gray-300 hover:700 font-medium py-2.5 px-5 rounded-lg shadow-sm hover:shadow transition-all flex items-center gap-2">
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
        @forelse($notes as $note)
            <div
                class="min-w-[320px] basis-[320px] h-[340px] flex grow flex-col py-4 px-5 bg-white rounded-xl shadow-md border border-gray-200 hover:shadow-lg transition-all">
                <div class="flex items-start justify-between mb-6">
                    <div class="w-8 flex-shrink-0 self-stretch">
                        <!-- Иконка заметки -->
                        <x-leaf class="{{ $note->icon_color_class }}" />
                    </div>

                    <div class="flex
                            flex-1 ml-3 flex-col">
                        <h3 class="font-bold text-lg text-gray-900">{{ $note->title }}</h3>
                        <p class="text-xs text-gray-500">
                            @if ($note->created_at->eq($note->updated_at))
                                Создано:
                            @else
                                Обновлено:
                            @endif
                            {{ $note->updated_at->locale('ru')->isoFormat('D MMMM YYYY') }}
                        </p>
                    </div>

                    <div class="self-stretch flex flex-end items-baseline">
                        <x-star :active="$note->is_favorite" size="30px"
                            wire:click.debounce.500ms="toggleFavorite({{ $note->id }})" />
                    </div>
                </div>

                <div class="flex-grow flex h-[170px] {{ $note->isChecklist() ? 'justify-center' : 'justify-start' }}">
                    @if ($note->isChecklist())
                        @php
                            $progress = $this->getChecklistProgress($note->id);
                            $percentage = $progress['percentage'];
                            $completed = $progress['completed'];
                            $total = $progress['total'];
                            $color = $progress['color'];
                            // Длина окружности: 2 * π * r = 2 * 3.14159 * 45 ≈ 283
                            $PI = 3.14159;
                            $radius = 45;
                            $circumference = 2 * $PI * $radius;
                            $offset = $circumference - ($percentage / 100) * $circumference;
                        @endphp
                        <div class="text-center">
                            <h4 class="mb-4 text-center font-medium text-gray-700">Прогресс выполнения</h4>
                            <div class="flex items-center gap-4 justify-center">
                                <!-- Progress Circle Container -->
                                <div class="relative w-[100px] h-[100px]">
                                    <svg viewBox="0 0 100 100" class="w-full h-full"
                                        style="transform: rotate(-90deg); display: block;">
                                        <!-- Background Circle -->
                                        <circle cx="50" cy="50" r="45" class="fill-none stroke-gray-200"
                                            stroke-width="8" stroke-linecap="round" />
                                        <!-- Progress Circle -->
                                        <circle cx="50" cy="50" r="45"
                                            class="fill-none transition-all duration-500" stroke-width="8"
                                            stroke-linecap="round" stroke-dasharray="283"
                                            stroke-dashoffset="{{ number_format($offset, 1, '.', '') }}"
                                            stroke="{{ $color }}" />
                                    </svg>
                                    <div
                                        class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-center">
                                        <span class="font-bold text-xl text-gray-900">{{ $percentage }}%</span>
                                    </div>
                                </div>
                                <div class="max-w-[120px] text-center">
                                    <p class="text-sm text-gray-600">{{ $completed }} из {{ $total }} задач
                                        выполнено</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="text-gray-700 h-[170px] overflow-hidden break-words">{!! nl2br(e($note->preview)) !!}</p>
                    @endif
                </div>

                <div class="flex justify-between border-t border-gray-200 pt-5 mt-auto">
                    <button wire:click="createFolder({{ $note->id }})"
                        class="bg-white border border-gray-300 hover:700 font-medium py-2 px-4 rounded-lg shadow-sm hover:shadow transition-all flex items-center gap-2">
                        <i data-lucide="{{ $note->folder ? $note->folder->icon : '' }}" class="w-4 h-4"></i>
                        {{ $note->folder ? $note->folder->title : 'Без папки' }}
                    </button>

                    @if ($note->isChecklist())
                        <button wire:click="openChecklist({{ $note->id }})"
                            class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                            <span>Открыть</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    @else
                        <button wire:click="openNote({{ $note->id }})"
                            class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                            <span>Открыть</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <!-- Состояние: нет заметок -->
            <div class="w-full flex items-center justify-center py-20">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-100 mb-6">
                        <i data-lucide="file-text" class="w-10 h-10 text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Заметок пока нет</h3>
                    <p class="text-gray-500 mb-6 max-w-md mx-auto">
                        Создайте первую заметку, чтобы увидеть её здесь
                    </p>
                    <button wire:click="createNote"
                        class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium py-2.5 px-6 rounded-lg shadow-md hover:shadow-lg transition-all inline-flex items-center gap-2">
                        <i data-lucide="plus" class="w-5 h-5"></i>
                        Создать заметку
                    </button>
                </div>
            </div>
        @endforelse
    </div>
</div>
