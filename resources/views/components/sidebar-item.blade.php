@props(['icon', 'label', 'active' => false, 'wireClick', 'count' => null, 'isExpanded' => false])

<a href="#" wire:click.prevent="{{ $wireClick }}"
    {{ $attributes->merge([
        'class' =>
            'flex items-center w-full px-4 py-3 rounded-lg text-gray-700 transition-all group/item ' .
            ($active
                ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white'
                : 'hover:bg-gray-100 hover:text-indigo-600'),
    ]) }}>
    <i data-lucide="{{ $icon }}" class="w-6 h-6 flex-shrink-0"></i>
    <span
        class="ml-3 font-medium whitespace-nowrap opacity-0 {{ $isExpanded ? 'opacity-100' : 'group-hover:opacity-100' }} transition-opacity duration-200">
        {{ $label }}
    </span>
    @if (!is_null($count))
        <span
            class="ml-auto bg-indigo-100 text-indigo-700 text-xs font-medium px-2 py-0.5 rounded-full opacity-0 {{ $isExpanded ? 'opacity-100' : 'group-hover:opacity-100' }} transition-opacity">
            {{ $count }}
        </span>
    @endif
</a>
