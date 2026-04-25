@props([
    'href' => '#',
    'external' => false,
])

<a href="{{ $href }}"
    {{ $attributes->merge(['class' => 'font-semibold text-indigo-600 hover:text-indigo-700 transition-colors']) }}
    {{ $external ? 'target="_blank" rel="noopener noreferrer"' : '' }}>
    {{ $slot }}
</a>
