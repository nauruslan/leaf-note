<div class="w-full flex items-center justify-center py-20">
    <div class="text-center">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-100 mb-6">
            <i data-lucide="{{ $icon }}" class="w-10 h-10 text-gray-400"></i>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $title }}</h3>
        <p class="text-gray-500 mb-6 max-w-md mx-auto">
            {{ $description }}
        </p>
        @if (isset($buttonText) && $buttonText)
            <button
                @if (isset($buttonAction) && $buttonAction) wire:click="{{ $buttonAction }}" @endif
                class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium py-2.5 px-5 rounded-lg shadow-md hover:shadow-lg transition-all inline-flex items-center gap-2"
            >
                @if (isset($buttonIcon) && $buttonIcon)
                    <i data-lucide="{{ $buttonIcon }}" class="w-4 h-4"></i>
                @endif
                {{ $buttonText }}
            </button>
        @endif
    </div>
</div>
