@props([
    'id' => 'dropdown-' . uniqid(),
    'label' => 'Выберите папку',
    'options' => [],
    'selected' => null,
    'width' => '150px',
    'wireModel' => null,
    'live' => true,
])

@php
    if (empty($options)) {
        $options = [];
    }
    // Определяем, выбранный текст
    $selectedText = $label;
    foreach ($options as $option) {
        if ($option['value'] == $selected) {
            $selectedText = $option['text'];
            break;
        }
    }
@endphp

<div class="flex items-center gap-2" data-dropdown-container>
    @if ($slot->isNotEmpty())
        <span class="whitespace-nowrap text-sm font-medium text-gray-700">{{ $slot }}</span>
    @endif

    <div wire:ignore>
        <div class="custom-select" id="{{ $id }}" style="width: {{ $width }}" data-dropdown>
            <div class="custom-select-trigger" data-dropdown-trigger>
                <span class="custom-select-label">{{ $selectedText }}</span>
                <span style="font-size: 10px; opacity: 0.6">▼</span>
            </div>

            <div class="custom-select-dropdown" data-dropdown-menu>
                @foreach ($options as $option)
                    <div class="custom-select-item @if ($selected == $option['value']) selected @endif"
                        data-value="{{ $option['value'] }}" data-dropdown-item>
                        {{ $option['text'] }}
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Hidden input for Livewire binding -->
    @if ($wireModel)
        <input type="hidden" value="{{ $selected }}" wire:model{{ $live ? '.live' : '' }}="{{ $wireModel }}"
            data-dropdown-input>
    @else
        <input type="hidden" value="{{ $selected }}" data-dropdown-input>
    @endif
</div>

@pushOnce('styles')
    <link rel="stylesheet" href="{{ asset('css/dropdown.css') }}">
@endPushOnce

@pushOnce('scripts')
    <script src="{{ asset('js/dropdown.js') }}" defer></script>
@endPushOnce
