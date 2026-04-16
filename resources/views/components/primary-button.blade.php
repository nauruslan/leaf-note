<button
    {{ $attributes->merge([
        'type' => 'button',
        'class' =>
            'bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium h-10 px-5 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2',
    ]) }}>
    {{ $slot }}
</button>