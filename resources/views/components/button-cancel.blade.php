<button
    {{ $attributes->merge([
        'type' => 'button',
        'class' =>
            'bg-gradient-to-r from-rose-500 to-red-600 hover:from-rose-600 hover:to-red-700 text-white font-medium py-2.5 px-5 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center gap-2',
    ]) }}>
    {{ $slot }}
</button>
