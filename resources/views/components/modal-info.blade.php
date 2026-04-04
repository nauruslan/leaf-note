@props([
    'show' => false,
    'title' => 'Информация',
    'description' => '',
    'okText' => 'Ок',
    'okMethod' => 'closeModal',
])

@if ($show)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
            <div class="flex items-start gap-4">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-100 mb-6">
                    <i data-lucide="shield-alert" class="w-8 h-8 text-indigo-600"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-xl font-bold text-gray-900">{{ $title }}</h3>
                    @if ($description)
                        <p class="text-gray-600 mt-2">{{ $description }}</p>
                    @endif
                </div>
            </div>
            <div class="flex justify-center mt-6">
                <button type="button"
                    class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium py-2.5 px-8 rounded-lg shadow-md hover:shadow-lg transition-all"
                    wire:click="{{ $okMethod }}">
                    {{ $okText }}
                </button>
            </div>
        </div>
    </div>
@endif
