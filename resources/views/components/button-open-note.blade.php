@props([
    'wireClick' => null,
    'isLoading' => false,
])

<button
    {{ $attributes->merge([
        'wire:click' => $wireClick,
        'type' => 'button',
        'class' =>
            'bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 h-10',
    ]) }}>
    <span>Открыть</span>
    @if ($isLoading)
        <x-loader class="w-4 h-4 flex-shrink-0 animate-spin" />
    @else
        <i data-lucide="arrow-right" class="w-4 h-4"></i>
    @endif
</button>
