@props([
    'heading',
    'subheading' => null,
    'showSearch' => false,
    'searchWireModel' => 'search',
    'searchWidth' => 'w-64',
    'section' => null,
])

<header class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-b-xl shadow-md p-5">
        <div class="flex items-center justify-between">
            <div>
                <h1
                    class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                    {{ $heading }}
                </h1>
                @if ($subheading)
                    <p class="text-sm text-gray-500 mt-0.5">{{ $subheading }}</p>
                @elseif($section = 'folder')
                    <div class="flex items-center gap-3 mt-1">
                        <button class="text-gray-500 hover:text-indigo-600 focus:outline-none" title="Редактировать папку"
                            wire:click="openEditFolder({{ $this->folder->id }})">
                            Редактировать
                        </button>|
                        <button class="text-gray-500 hover:text-red-600 focus:outline-none" title="Удалить папку"
                            wire:click="confirmDeletion">Удалить
                        </button>
                    </div>
                @endif
            </div>

            @if ($showSearch)
                <x-search wireModel="{{ $searchWireModel }}" />
            @endif
        </div>
    </div>
</header>
