@props([
    'id' => 'search-' . uniqid(),
    'placeholder' => 'Поиск',
    'wireModel' => 'search',
    'debounce' => '300',
    'width' => 'w-64',
    'class' => '',
])

@php
    $inputName = $wireModel ? str_replace('.', '-', $wireModel) : 'search';
@endphp

<div class="relative {{ $width }} {{ $class }}" wire:ignore data-search-container>
    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
    </div>

    <input type="text" id="{{ $id }}" name="{{ $inputName }}" placeholder="{{ $placeholder }}"
        wire:model.live.debounce.{{ $debounce }}ms="{{ $wireModel }}"
        class="pl-10 pr-10 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 w-full transition-all"
        autocomplete="off" data-search-input>

    <!-- Крестик очистки -->
    <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center hidden" data-search-clear
        aria-label="Очистить поиск">
        <svg class="w-4 h-4 text-gray-400 hover:text-gray-600 transition-colors" fill="none" stroke="currentColor"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
    </button>
</div>
