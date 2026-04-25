@props([
    'href' => '#',
    'external' => false,
    'variant' => 'default',
])

@php
    $classes = match ($variant) {
        'footer'
            => 'group flex items-center gap-1.5 text-sm font-medium text-gray-600 hover:text-indigo-600 transition-colors duration-200',
        default => 'font-semibold text-indigo-600 hover:text-indigo-700 transition-colors',
    };
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}
    {{ $external ? 'target="_blank" rel="noopener noreferrer"' : '' }}>
    {{ $slot }}
</a>
