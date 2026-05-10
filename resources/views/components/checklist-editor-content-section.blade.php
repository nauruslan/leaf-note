@props([
    'title' => '',
    'content' => null,
    'checklist' => null,
    'editorId' => 'checklist-editor',
    'contentInputId' => 'checklist-content-input',
    'contentDebounce' => null,
])

<div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 pb-6">
    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden min-h-[600px] flex flex-col">
        <!-- Checklist Title Input -->
        <div class="px-6 pt-6 pb-2 border-b border-gray-100">
            <input type="text" wire:model.live.debounce.300ms="title" placeholder="Название списка"
                class="p-0 w-full text-2xl font-bold text-gray-900 placeholder-gray-400 border-none focus:outline-none focus:ring-0 bg-transparent">
        </div>

        <!-- Progress -->
        <div wire:ignore>
            <div id="checklist-progress-bar" class="px-6 py-4 border-b border-gray-200 flex justify-center">
                <!-- Progress bar will be initialized by JS -->
            </div>
        </div>

        <!-- Checklist Editor -->
        <div wire:ignore>
            <div class="flex-grow p-6">
                @php
                    $editorContent = [];
                    if ($content) {
                        if (is_array($content)) {
                            $editorContent = $content;
                        } elseif (is_string($content)) {
                            $decoded = json_decode($content, true);
                            if (is_array($decoded)) {
                                $editorContent = $decoded;
                            }
                        }
                    }
                @endphp
                <div id="{{ $editorId }}" data-content='@json($editorContent)'
                    class="checklist-editor prose prose-indigo max-w-none focus:outline-none min-h-[400px] text-gray-700">
                </div>
            </div>
        </div>

        <!-- Hidden input for content synchronization -->
        <input type="hidden" id="{{ $contentInputId }}"
            wire:model.live{{ $contentDebounce ? '.debounce.' . $contentDebounce : '' }}="content">

        <!-- Footer Info -->
        <div
            class="px-6 py-3 border-t border-gray-200 bg-gray-50/50 flex justify-between items-center text-xs text-gray-500">
            <div wire:ignore class="flex items-center gap-4">
                <span>Создано: {{ $checklist?->created_at?->translatedFormat('d F Y') ?? 'только что' }}</span>
                <span>•</span>
                <span>Изменено: {{ $checklist?->updated_at?->translatedFormat('d F Y') ?? 'только что' }}</span>
                <span>•</span>
                <span data-task-count>0 задач</span>
            </div>
            <div class="flex items-center" id="autosave-container">
                <div id="autosave-spinner" class="flex items-center" wire:loading
                    wire:target="title,content,folderId,safeId">
                    <svg class="animate-spin h-3 w-3 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>
