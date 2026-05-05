@props(['height' => 'h-10', 'bgColor' => 'bg-gray-50'])

@php
    $bgHoverClass = match ($bgColor) {
        'bg-gray-50' => 'hover:bg-gray-100',
        'bg-white' => 'hover:bg-gray-50',
        default => 'hover:bg-gray-100',
    };
@endphp

<button
    {{ $attributes->merge(['type' => 'button'])->class(
        'text-gray-700 font-medium ' .
        $height .
        ' px-5 rounded-lg border border-gray-300 ' .
        $bgColor .
        ' ' .
        $bgHoverClass .
        ' transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed'
    ) }}>
    {{ $slot }}
</button>
