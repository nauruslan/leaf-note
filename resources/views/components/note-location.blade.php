@props([
    'button' => false,
])

@php
    $baseClasses =
        'bg-white border border-gray-300 font-medium py-2 px-4 rounded-lg shadow-sm transition-all flex items-center justify-center gap-2 h-10';
    $stateClasses = $button ? 'hover:bg-gray-100 hover:shadow cursor-pointer' : 'cursor-default';
@endphp

@if ($button)
    <button {{ $attributes->merge(['class' => $baseClasses . ' ' . $stateClasses]) }}>
        {{ $slot }}
    </button>
@else
    <div {{ $attributes->merge(['class' => $baseClasses . ' ' . $stateClasses]) }}>
        {{ $slot }}
    </div>
@endif
