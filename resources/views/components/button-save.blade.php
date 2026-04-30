@php
    // Определяем целевой метод для wire:loading из атрибутов
    $target = $attributes->get('target') ?? 'save';
@endphp
<button
    {{ $attributes->merge([
        'type' => 'button',
        'class' =>
            'inline-flex items-center justify-center bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed',
        'style' => 'width: 120px; height: 36px;',
    ]) }}
    wire:loading.attr="disabled" wire:target="{{ $target }}">
    <!-- Текст "Сохранить" - скрывается при загрузке -->
    <span wire:loading.remove wire:target="{{ $target }}">Сохранить</span>
    <!-- Loader - показывается при загрузке, по центру кнопки -->
    <x-loader wire:loading wire:target="{{ $target }}" class="w-4 h-4" />
</button>
