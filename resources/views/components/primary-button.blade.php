@props(['height' => 'h-11', 'variant' => 'default'])

@php
    $variantClasses = match ($variant) {
        'danger' => 'bg-red-600 hover:bg-red-700 text-white',
        'default'
            => 'bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white',
        default
            => 'bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white',
    };
@endphp

<button
    {{ $attributes->merge(['type' => 'button'])->class(
            $variantClasses .
                ' font-semibold ' .
                $height .
                ' min-w-[80px] px-5 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed',
        ) }}>
    {{ $slot }}
</button>
