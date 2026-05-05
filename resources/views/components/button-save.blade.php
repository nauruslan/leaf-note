@props([
    'text' => 'Сохранить',
    'loadingText' => null,
    'target' => 'save',
    'height' => 'h-10',
])

@php
    $loadingText = $loadingText ?? 'Сохранение...';
@endphp

<button
    {{ $attributes->merge([
        'type' => 'button',
        'class' =>
            'inline-flex items-center justify-center bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed ' .
            $height .
            ' w-[120px]',
    ]) }}
    wire:loading.attr="disabled" wire:target="{{ $target }}">
    <!-- Текст кнопки - скрывается при загрузке -->
    <span wire:loading.remove wire:target="{{ $target }}">{{ $text }}</span>
    <!-- Loader - показывается при загрузке, по центру кнопки -->
    <x-loader wire:loading wire:target="{{ $target }}" class="w-4 h-4" />
</button>
