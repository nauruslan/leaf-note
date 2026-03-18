@props([
    'title' => 'Удалить?',
    'description' => 'Элемент будет перемещен в корзину. Вы сможете восстановить его позже.',
    'confirmingDeletion' => false,
    'closeMethod' => 'closeModal',
    'deleteMethod' => 'deleteFolder',
])

@if ($confirmingDeletion)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
            <h3 class="text-xl font-bold text-gray-900">{{ $title }}</h3>
            <p class="text-gray-600 mt-2">{{ $description }}</p>
            <div class="flex justify-end gap-4 mt-6">
                <button type="button"
                    class="px-5 py-2.5 text-gray-700 font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors"
                    wire:click="{{ $closeMethod }}">
                    Отменить
                </button>
                <button type="button"
                    class="px-5 py-2.5 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition-colors"
                    wire:click="{{ $deleteMethod }}">
                    Удалить
                </button>
            </div>
        </div>
    </div>
@endif
