@props([
    'show' => false,
    'title' => 'Подтверждение',
    'description' => '',
    'confirmText' => 'Подтвердить',
    'cancelText' => 'Отмена',
    'confirmMethod' => 'confirm',
    'cancelMethod' => 'cancel',
    'confirmColor' => 'indigo',
])

@php
    $colorClasses = match ($confirmColor) {
        'red' => 'bg-red-600 hover:bg-red-700',
        'indigo'
            => 'bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium py-2.5 px-5 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center gap-2',
        default
            => 'bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium py-2.5 px-5 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center gap-2',
    };
@endphp

@if ($show)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
            <h3 class="text-xl font-bold text-gray-900">{{ $title }}</h3>
            @if ($description)
                <p class="text-gray-600 mt-2">{{ $description }}</p>
            @endif
            <div class="flex justify-end gap-4 mt-6">
                <button type="button"
                    class="px-5 py-2.5 text-gray-700 font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors"
                    wire:click="{{ $cancelMethod }}">
                    {{ $cancelText }}
                </button>
                <button type="button"
                    class="px-5 py-2.5 {{ $colorClasses }} text-white font-medium rounded-lg transition-colors"
                    wire:click="{{ $confirmMethod }}">
                    {{ $confirmText }}
                </button>
            </div>
        </div>
    </div>
@endif
