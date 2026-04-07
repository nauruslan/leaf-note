{{-- @props(['note', 'color' => 'black'])

<div
    {{ $attributes->merge(['class' => 'min-w-[320px] h-[340px] flex flex-col py-4 px-5 bg-white rounded-xl shadow-md border border-gray-200 hover:shadow-lg transition-all relative']) }}>

    <!-- Overlay элемент с динамическим цветом -->
    @php
        // $colorMap = [
        //     'red-500' => 'rgb(239, 68, 68)',
        //     'orange-500' => 'rgb(249, 115, 22)',
        //     'yellow-500' => 'rgb(234, 179, 8)',
        //     'green-500' => 'rgb(34, 197, 94)',
        //     'blue-500' => 'rgb(59, 130, 246)',
        //     'indigo-500' => 'rgb(99, 102, 241)',
        //     'purple-500' => 'rgb(168, 85, 247)',
        //     'pink-500' => 'rgb(236, 72, 153)',
        //     'gray-500' => 'rgb(107, 114, 128)',
        //     'black-500' => 'rgb(0, 0, 0)',
        //     'white' => 'rgb(255, 255, 255)',
        // ];
        // $rgbColor = $colorMap[$color] ?? 'rgb(0, 0, 0)';
        $rgbColor = $color ?? 'rgb(0, 0, 0)';
    @endphp

    <div style="background-color: rgba({{ substr($rgbColor, 4, -1) }}, 0.3);"
        class="absolute top-0 left-0 right-0 h-[70px] rounded-t-xl pointer-events-none z-0">
    </div>

    <div class="flex items-start justify-between mb-4 relative z-10">
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

    <div class="flex justify-between border-t border-gray-200 pt-5 mt-auto">
        @if ($note->isInFolder())
            <button wire:click="openFolder({{ $note->folder_id }})"
                class="bg-white border border-gray-300 hover:700 font-medium py-2 px-4 rounded-lg shadow-sm hover:shadow transition-all flex items-center gap-2">
                <i data-lucide="{{ $note->folder ? $note->folder->icon : '' }}" class="w-4 h-4"></i>
                {{ $note->folder->title }}
            </button>
        @elseif ($note->isInArchive())
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
</div> --}}


@props(['note', 'color' => 'black'])

<div
    {{ $attributes->merge(['class' => 'min-w-[320px] h-[340px] flex flex-col py-4 px-5 bg-white rounded-xl shadow-md border border-gray-200 hover:shadow-lg transition-all relative']) }}>

    <!-- Overlay элемент с динамическим цветом -->
    <div class="absolute top-0 left-0 right-0 h-[70px] bg-{{ $color }}/20 rounded-t-xl pointer-events-none z-0">
    </div>

    <div class="flex items-start justify-between mb-4 relative z-10">
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

    <div class="flex justify-between border-t border-gray-200 pt-5 mt-auto">
        @if ($note->isInFolder())
            <button wire:click="openFolder({{ $note->folder_id }})"
                class="bg-white border border-gray-300 hover:700 font-medium py-2 px-4 rounded-lg shadow-sm hover:shadow transition-all flex items-center gap-2">
                <i data-lucide="{{ $note->folder ? $note->folder->icon : '' }}" class="w-4 h-4"></i>
                {{ $note->folder->title }}
            </button>
        @elseif ($note->isInArchive())
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
