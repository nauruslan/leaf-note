@props(['item', 'type' => 'folder'])

@php
    $deletedAt = $item->moved_to_trash_at;
    $notesCount = $item->trashedNotes->count();
@endphp

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
