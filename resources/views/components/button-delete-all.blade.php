<button
    {{ $attributes->merge([
        'type' => 'button',
        'class' =>
            'bg-white border border-gray-300 hover:bg-red-50 text-red-600 font-medium py-2.5 px-5 rounded-lg shadow-sm hover:shadow transition-all flex items-center justify-center gap-2 h-10',
    ]) }}>
    <i data-lucide="trash-2" class="w-4 h-4"></i>
    <span>Очистить корзину</span>
</button>
