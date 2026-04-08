<button
    {{ $attributes->merge([
        'type' => 'button',
        'class' =>
            'bg-gradient-to-r from-rose-500 to-red-600 hover:from-rose-600 hover:to-red-700 text-white font-medium h-10 px-5 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2',
    ]) }}>
    <i data-lucide="trash-2" class="w-4 h-4"></i>
    <span>Удалить</span>
</button>
