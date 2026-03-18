<button
    {{ $attributes->merge([
        'type' => 'button',
        'class' =>
            'bg-white border border-gray-300 hover:700 font-medium py-2.5 px-5 rounded-lg shadow-sm hover:shadow transition-all flex items-center gap-2',
    ]) }}>
    {{ $slot }}
</button>
