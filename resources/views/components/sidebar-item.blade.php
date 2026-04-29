@props([
    'icon',
    'label',
    'active' => false,
    'wireClick',
    'count' => null,
    'isExpanded' => false,
    'isLoading' => false,
])

<a href="#" wire:click.prevent="{{ $wireClick }}"
    {{ $attributes->merge([
        'class' =>
            'sidebar-item flex items-center w-full px-4 py-3 rounded-lg text-gray-700 transition-all group/item ' .
            ($active
                ? 'sidebar-active-item bg-gradient-to-r from-indigo-600 to-purple-600 text-white'
                : 'hover:bg-gray-100 hover:text-indigo-600'),
    ]) }}>
    @if ($isLoading)
        <x-loader class="w-6 h-6 flex-shrink-0 animate-spin" />
    @else
        <i data-lucide="{{ $icon }}" class="w-6 h-6 flex-shrink-0"></i>
    @endif
    <span
        class="ml-3 font-medium whitespace-nowrap opacity-0 {{ $isExpanded ? 'opacity-100' : 'group-hover:opacity-100' }} transition-opacity duration-200">
        {{ $label }}
    </span>
    @if (!is_null($count))
        <span
            class="ml-auto bg-indigo-100 text-indigo-700 text-xs font-medium w-8 h-5 flex items-center justify-center rounded-full opacity-0 {{ $isExpanded ? 'opacity-100' : 'group-hover:opacity-100' }} transition-opacity">
            {{ $count > 99 ? '+99' : $count }}
        </span>
    @endif
</a>
