<button
    {{ $attributes->merge([
        'type' => 'button',
        'class' =>
            'bg-white border border-gray-300 hover:bg-gray-100 font-medium h-10 px-5 rounded-lg shadow-sm hover:shadow transition-all flex items-center justify-center gap-2',
    ]) }}>
    <i data-lucide="arrow-left" class="w-4 h-4"></i>
    <span>Назад</span>
</button>
