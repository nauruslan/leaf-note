<button
    {{ $attributes->merge([
        'type' => 'button',
        'class' =>
            'bg-white border border-gray-300 hover:bg-gray-100 font-medium h-10 px-5 rounded-lg shadow-sm hover:shadow transition-all flex items-center justify-center gap-2',
    ]) }}>
    <i data-lucide="mail" class="w-4 h-4 text-indigo-600"></i>
    <span>Сбросить по email</span>
</button>
