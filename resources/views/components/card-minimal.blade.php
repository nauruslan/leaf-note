@props(['note'])

<div
    {{ $attributes->merge(['class' => 'min-w-[320px] h-[340px] flex flex-col py-4 px-5 bg-white rounded-xl shadow-md border border-gray-200 hover:shadow-lg transition-all']) }}>
    <div class="flex items-start justify-between mb-4">
        <div class="flex flex-1 flex-col">
            <h3 class="font-bold text-lg text-gray-900">{{ $note->title ?: 'Без названия' }}</h3>
            <p class="text-xs text-gray-500">
                @if ($note->created_at->eq($note->updated_at))
                    Создано:
                @else
                    Обновлено:
                @endif
                {{ $note->updated_at->locale('ru')->isoFormat('D MMMM YYYY') }}
            </p>
        </div>
    </div>

    <div class="flex-grow flex h-[170px] {{ $note->isChecklist() ? 'justify-center' : 'justify-start' }}">
        @if ($note->isChecklist())
            <x-checklist :note="$note" />
        @else
            <x-note :note="$note" />
        @endif
    </div>

    <div class="flex justify-between border-t border-gray-200 pt-4 mt-auto">
        @if ($note->isInArchive())
            <div
                class="bg-white border border-gray-300 font-medium py-2 px-4 rounded-lg shadow-sm transition-all flex items-center gap-2">
                <i data-lucide="archive" class="w-4 h-4"></i>
                Архив
            </div>
        @elseif ($note->isInSafe())
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

        <button wire:click="openItem({{ $note->id }})"
            class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center gap-2">
            <span>Открыть</span>
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </button>
    </div>
</div>
