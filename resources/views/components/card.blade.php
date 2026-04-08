@props(['item', 'color' => 'black', 'section' => 'default', 'type' => 'note'])

@php
    $isChecklist = $item->type === 'checklist';
    $deletedAt = $item->moved_to_trash_at;
    if ($type === 'folder') {
        $notesCount = $item->trashedNotes->count();
    }
@endphp

@if ($section === 'trash' && $type === 'folder')
    <div
        {{ $attributes->merge(['class' => 'min-w-[320px] h-[340px] flex flex-col py-4 px-5 bg-gradient-to-b from-amber-200 to-amber-300 rounded-xl shadow-md border border-amber-400 hover:shadow-lg transition-all']) }}>
        <div class="flex items-start justify-between mb-4">
            <div class="flex flex-1 flex-col">
                <h3 class="font-bold text-lg text-amber-900">Папка: {{ $item->title ?: 'Без названия' }}</h3>
                <p class="text-xs text-amber-700">
                    Удалено: {{ $deletedAt?->locale('ru')->isoFormat('D MMMM YYYY HH:mm') ?? 'неизвестно' }}
                </p>
            </div>
        </div>

        <div class="flex-grow flex items-center justify-center">
            <p class="text-amber-800 text-lg font-medium">
                {{ $notesCount }}
                {{ trans_choice('заметка|заметки|заметок', $notesCount) }}
            </p>
        </div>

        <div class="flex justify-between border-t border-amber-400/50 pt-4 mt-auto">
            <button wire:click="$dispatch('restoreItem', { id: {{ $item->id }}, type: '{{ $type }}' })"
                class="bg-white hover:bg-gray-100 text-gray-700 font-medium py-2 px-4 rounded-lg shadow-sm hover:shadow transition-all flex items-center gap-2">
                <i data-lucide="refresh-ccw" class="w-4 h-4"></i>
                <span>Восстановить</span>
            </button>

            <button wire:click="$dispatch('deleteItem', { id: {{ $item->id }}, type: '{{ $type }}' })"
                class="bg-red-500 hover:bg-red-600 text-white font-medium py-2 px-4 rounded-lg shadow-sm hover:shadow transition-all flex items-center gap-2">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
                <span>Удалить</span>
            </button>
        </div>
    </div>
@else
    <div
        {{ $attributes->merge(['class' => 'min-w-[320px] h-[340px] flex flex-col py-4 px-5 bg-white rounded-xl shadow-md border border-gray-200 hover:shadow-lg transition-all relative']) }}>

        <!-- Overlay элемент с динамическим цветом -->
        <div
            class="absolute top-0 left-0 right-0 h-[70px] bg-{{ $color }}/20 rounded-t-xl pointer-events-none z-0">
        </div>

        <div class="flex items-start justify-between mb-4 relative z-10">
            <div class="flex flex-1 flex-col">
                <h3 class="font-bold text-lg text-gray-900">{{ $item->title ?: 'Без названия' }}</h3>
                <p class="text-xs text-gray-500">
                    @if ($section === 'trash')
                        Удалено: {{ $deletedAt?->locale('ru')->isoFormat('D MMMM YYYY HH:mm') ?? 'неизвестно' }}
                    @else
                        @if ($item->created_at->eq($item->updated_at))
                            Создано:
                        @else
                            Обновлено:
                        @endif
                        {{ $item->updated_at->locale('ru')->isoFormat('D MMMM YYYY') }}
                    @endif
                </p>
            </div>
        </div>

        <div class="flex-grow flex h-[170px] {{ $item->isChecklist() ? 'justify-center' : 'justify-start' }}">
            @if ($item->isChecklist())
                <x-checklist :note="$item" />
            @else
                <x-note :note="$item" />
            @endif
        </div>

        @if ($section === 'trash')
            <div class="flex justify-between border-t border-gray-200 pt-4 mt-auto">
                <button wire:click="$dispatch('restoreItem', { id: {{ $item->id }}, type: 'note' })"
                    class="bg-white border border-gray-300 hover:bg-gray-50 font-medium py-2 px-4 rounded-lg shadow-sm hover:shadow transition-all flex items-center gap-2">
                    <i data-lucide="refresh-ccw" class="w-4 h-4"></i>
                    <span>Восстановить</span>
                </button>

                <button wire:click="$dispatch('deleteItem', { id: {{ $item->id }}, type: 'note' })"
                    class="bg-red-500 hover:bg-red-600 text-white font-medium py-2 px-4 rounded-lg shadow-sm hover:shadow transition-all flex items-center gap-2">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                    <span>Удалить</span>
                </button>
            </div>
        @else
            <div class="flex justify-between border-t border-gray-200 pt-5 mt-auto">
                @if ($item->isInFolder() && $section === 'folder')
                    <div
                        class="bg-white border border-gray-300 font-medium py-2 px-4 rounded-lg shadow-sm transition-all flex items-center gap-2">
                        <i data-lucide="{{ $item->folder ? $item->folder->icon : '' }}" class="w-4 h-4"></i>
                        {{ $item->folder->title }}
                    </div>
                @elseif ($item->isInFolder())
                    <button wire:click="openFolder({{ $item->folder_id }})"
                        class="bg-white border border-gray-300 hover:700 font-medium py-2 px-4 rounded-lg shadow-sm hover:shadow transition-all flex items-center gap-2">
                        <i data-lucide="{{ $item->folder ? $item->folder->icon : '' }}" class="w-4 h-4"></i>
                        {{ $item->folder->title }}
                    </button>
                @elseif ($item->isInArchive())
                    <div
                        class="bg-white border border-gray-300 font-medium py-2 px-4 rounded-lg shadow-sm transition-all flex items-center gap-2">
                        <i data-lucide="archive" class="w-4 h-4"></i>
                        Архив
                    </div>
                @elseif ($item->isInSafe())
                    <div
                        class="bg-white border border-gray-300 font-medium py-2 px-4 rounded-lg shadow-sm transition-all flex items-center gap-2">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                        Сейф
                    </div>
                @else
                    <div
                        class="bg-white border border-gray-300 font-medium py-2 px-4 rounded-lg shadow-sm transition-all flex items-center gap-2 text-gray-500">
                        <i data-lucide="inbox" class="w-4 h-4"></i>
                        Без папки
                    </div>
                @endif


                <button wire:click="openNote({{ $item->id }})"
                    class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                    <span>Открыть</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>

            </div>
        @endif
    </div>
@endif
