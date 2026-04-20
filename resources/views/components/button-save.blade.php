@php
    // Определяем целевой метод для wire:loading из атрибутов
    $target = $attributes->get('target') ?? 'save';
@endphp
<button
    {{ $attributes->merge([
        'type' => 'button',
        'class' =>
            'bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium h-10 px-5 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed',
    ]) }}>
    <!-- Иконка save - скрывается при загрузке -->
    <i data-lucide="save" class="w-4 h-4" wire:loading.remove wire:target="{{ $target }}"></i>
    <!-- Loader - показывается при загрузке -->
    <x-loader wire:loading wire:target="{{ $target }}" class="w-4 h-4" />
    <span>Сохранить</span>
</button>
