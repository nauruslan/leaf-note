<button
    {{ $attributes->merge([
        'type' => 'button',
        'class' =>
            'bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white font-medium py-2.5 px-5 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center gap-2',
    ]) }}>
    {{ $slot }}
</button>
