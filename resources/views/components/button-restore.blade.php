<button
    {{ $attributes->merge([
        'type' => 'button',
        'class' =>
            'bg-white border border-gray-300 hover:bg-gray-50 font-medium h-10 px-4 rounded-lg shadow-sm hover:shadow transition-all flex items-center justify-center gap-2',
    ]) }}>
    <i data-lucide="history" class="w-4 h-4"></i>
    <span>Восстановить</span>
</button>
