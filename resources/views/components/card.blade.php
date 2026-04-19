@props(['item', 'color' => '#000000', 'section' => 'default', 'type' => 'note'])

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
            <x-button-restore wire:click="restoreItem({{ $item->id }}, 'folder')" />
            <x-button-delete wire:click="deleteItem({{ $item->id }}, 'folder')" />
        </div>
    </div>
@else
    <div
        {{ $attributes->merge(['class' => 'min-w-[320px] h-[340px] flex flex-col py-4 px-5 bg-white rounded-xl shadow-md border border-gray-200 hover:shadow-lg transition-all relative']) }}>

        <!-- Overlay элемент с динамическим цветом -->
        <div style="background-color: {{ $color }}50;"
            class="absolute top-0 left-0 right-0 h-[70px] rounded-t-xl pointer-events-none z-0">
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
                <x-button-restore wire:click="restoreItem({{ $item->id }}, 'note')" />
                <x-button-delete wire:click="deleteItem({{ $item->id }}, 'note')" />
            </div>
        @else
            <div class="flex justify-between border-t border-gray-200 pt-5 mt-auto">
                @if ($item->isInFolder() && $section === 'folder')
                    <x-note-location>
                        <i data-lucide="{{ $item->folder->icon ?? '' }}" class="w-4 h-4"></i>
                        {{ $item->folder->title }}
                    </x-note-location>
                @elseif ($item->isInFolder())
                    <x-note-location button wire:click="openFolder({{ $item->folder_id }})">
                        <i data-lucide="{{ $item->folder->icon ?? '' }}" class="w-4 h-4"></i>
                        {{ $item->folder->title }}
                    </x-note-location>
                @elseif ($item->isInArchive())
                    <x-note-location>
                        <i data-lucide="archive" class="w-4 h-4"></i>
                        Архив
                    </x-note-location>
                @elseif ($item->isInSafe())
                    <x-note-location>
                        <i data-lucide="lock" class="w-4 h-4"></i>
                        Сейф
                    </x-note-location>
                @else
                    <x-note-location>
                        <i data-lucide="inbox" class="w-4 h-4"></i>
                        Без папки
                    </x-note-location>
                @endif

                <x-button-open-note wire:click="openNote({{ $item->id }})" />

            </div>
        @endif
    </div>
@endif
