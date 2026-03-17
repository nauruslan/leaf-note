@if ($paginator->hasPages())
    <nav class="flex items-center justify-center gap-2 mb-6">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span
                class="w-10 h-10 rounded-lg border border-gray-300 bg-gray-100 text-gray-400 flex items-center justify-center shadow-sm cursor-not-allowed">
                <i data-lucide="chevron-left" class="w-5 h-5"></i>
            </span>
        @else
            <button wire:click="previousPage" wire:loading.attr="disabled"
                class="w-10 h-10 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-indigo-500 hover:text-indigo-600 transition-all flex items-center justify-center shadow-sm">
                <i data-lucide="chevron-left" class="w-5 h-5"></i>
            </button>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <span
                    class="w-10 h-10 rounded-lg border border-gray-300 bg-white text-gray-700 flex items-center justify-center font-medium shadow-sm">
                    {{ $element }}
                </span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <button wire:click="gotoPage({{ $page }})" wire:loading.attr="disabled"
                            class="w-10 h-10 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-medium shadow-md hover:from-indigo-700 hover:to-purple-700 transition-all flex items-center justify-center active:scale-[0.98]">
                            {{ $page }}
                        </button>
                    @else
                        <button wire:click="gotoPage({{ $page }})" wire:loading.attr="disabled"
                            class="w-10 h-10 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-indigo-500 hover:text-indigo-600 transition-all flex items-center justify-center font-medium shadow-sm">
                            {{ $page }}
                        </button>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <button wire:click="nextPage" wire:loading.attr="disabled"
                class="w-10 h-10 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-indigo-500 hover:text-indigo-600 transition-all flex items-center justify-center shadow-sm">
                <i data-lucide="chevron-right" class="w-5 h-5"></i>
            </button>
        @else
            <span
                class="w-10 h-10 rounded-lg border border-gray-300 bg-gray-100 text-gray-400 flex items-center justify-center shadow-sm cursor-not-allowed">
                <i data-lucide="chevron-right" class="w-5 h-5"></i>
            </span>
        @endif
    </nav>
@endif
