@props(['item', 'type' => 'note'])

@php
    $isFolder = $type === 'folder';
    $isChecklist = !$isFolder && $item->type === 'checklist';
    $isNote = !$isFolder && !$isChecklist;

    $typeLabel = $isChecklist ? 'Список' : 'Заметка';
    $deletedAt = $item->moved_to_trash_at;
@endphp

@if ($isFolder)
    <x-folder :item="$item" :type="$type" />
@else
    <div
        {{ $attributes->merge(['class' => 'min-w-[320px] basis-[320px] h-[340px] flex grow flex-col py-4 px-5 bg-white rounded-xl shadow-md border border-gray-200 hover:shadow-lg transition-all']) }}>
        <div class="flex items-start justify-between mb-4">
            <div class="flex flex-1 flex-col">
                <h3 class="font-bold text-lg text-gray-900">{{ $typeLabel }}: {{ $item->title ?: 'Без названия' }}
                </h3>
                <p class="text-xs text-gray-500">
                    Удалено: {{ $deletedAt?->locale('ru')->isoFormat('D MMMM YYYY HH:mm') ?? 'неизвестно' }}
                </p>
            </div>
        </div>

        <div class="flex-grow flex h-[170px] {{ $isChecklist ? 'justify-center' : 'justify-start' }}">
            @if ($isChecklist)
                <x-checklist :note="$item" />
            @else
                <x-note :note="$item" />
            @endif
        </div>

        <div class="flex justify-between border-t border-gray-200 pt-4 mt-auto">
            <button wire:click="$dispatch('restoreItem', { id: {{ $item->id }}, type: '{{ $type }}' })"
                class="bg-white border border-gray-300 hover:bg-gray-50 font-medium py-2 px-4 rounded-lg shadow-sm hover:shadow transition-all flex items-center gap-2">
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
@endif
