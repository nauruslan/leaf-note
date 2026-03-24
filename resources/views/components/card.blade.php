@props(['note'])

<div
    {{ $attributes->merge(['class' => 'min-w-[320px] basis-[320px] h-[340px] flex grow flex-col py-4 px-5 bg-white rounded-xl shadow-md border border-gray-200 hover:shadow-lg transition-all']) }}>
    <div class="flex items-start justify-between mb-6">
        <div class="w-8 flex-shrink-0 self-stretch">
            <!-- Иконка заметки -->
            <x-leaf class="{{ $note->icon_color_class }}" />
        </div>

        <div class="flex flex-1 ml-3 flex-col">
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
            <x-star :active="$note->is_favorite" size="30px" wire:click.debounce.500ms="toggleFavorite({{ $note->id }})" />
        </div>
    </div>

    <div class="flex-grow flex h-[170px] {{ $note->isChecklist() ? 'justify-center' : 'justify-start' }}">
        @if ($note->isChecklist())
            <x-checklist :note="$note" />
        @else
            <x-note :note="$note" />
        @endif
    </div>

    <div class="flex justify-between border-t border-gray-200 pt-5 mt-auto">
        <button wire:click="openFolder({{ $note->folder_id }})"
            class="bg-white border border-gray-300 hover:700 font-medium py-2 px-4 rounded-lg shadow-sm hover:shadow transition-all flex items-center gap-2">
            <i data-lucide="{{ $note->folder ? $note->folder->icon : '' }}" class="w-4 h-4"></i>
            {{ $note->folder ? $note->folder->title : 'Без папки' }}
        </button>

        <button wire:click="openItem({{ $note->id }})"
            class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center gap-2">
            <span>Открыть</span>
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </button>

    </div>
</div>
