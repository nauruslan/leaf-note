@props([
    'id' => 'dropdown-' . uniqid(),
    'label' => 'Выберите папку',
    'options' => [],
    'safes' => [],
    'archives' => [],
    'selected' => null,
    'width' => '150px',
    'wireModel' => null,
    'live' => true,
    'disabled' => false,
])

@php
    if (empty($options)) {
        $options = [];
    }
    if (empty($safes)) {
        $safes = [];
    }
    if (empty($archives)) {
        $archives = [];
    }
    // Определяем, выбранный текст
    $selectedText = $label;
    foreach ($options as $option) {
        if ((string) $selected === (string) $option['value']) {
            $selectedText = $option['text'];
            break;
        }
    }
    // Проверяем, выбран ли safe
    foreach ($safes as $safe) {
        if ((string) $selected === (string) $safe['value']) {
            $selectedText = $safe['text'];
            break;
        }
    }
    // Проверяем, выбран ли archive
    foreach ($archives as $archive) {
        if ((string) $selected === (string) $archive['value']) {
            $selectedText = $archive['text'];
            break;
        }
    }
@endphp

<div class="flex items-center gap-2" data-dropdown-container>
    @if ($slot->isNotEmpty())
        <span class="whitespace-nowrap text-sm font-medium text-gray-700">{{ $slot }}</span>
    @endif

    <div wire:ignore>
        <div {{ $attributes->merge(['class' => 'custom-select' . ($disabled ? ' disabled' : ''), 'id' => $id, 'style' => "width: {$width}", 'data-dropdown' => true]) }}
            @if ($disabled) data-disabled="true" @endif>
            <div class="custom-select-trigger h-10" data-dropdown-trigger>
                <span class="custom-select-label">{{ $selectedText }}</span>
                <span style="font-size: 10px; opacity: 0.6">▼</span>
            </div>

            <div class="custom-select-dropdown" data-dropdown-menu>
                @foreach ($options as $option)
                    <div class="custom-select-item @if ((string) $selected === (string) $option['value']) selected @endif"
                        data-value="{{ $option['value'] }}" data-dropdown-item>
                        {{ $option['text'] }}
                    </div>
                @endforeach
                @if (count($safes) > 0)
                    <div class="custom-select-divider"></div>
                    @foreach ($safes as $safe)
                        <div class="custom-select-item @if ((string) $selected === (string) $safe['value']) selected @endif"
                            data-value="{{ $safe['value'] }}" data-dropdown-item data-safe="true">
                            🔒 {{ $safe['text'] }}
                        </div>
                    @endforeach
                @endif
                @if (count($archives) > 0)
                    <div class="custom-select-divider"></div>
                    @foreach ($archives as $archive)
                        <div class="custom-select-item @if ((string) $selected === (string) $archive['value']) selected @endif"
                            data-value="{{ $archive['value'] }}" data-dropdown-item data-archive="true">
                            📦 {{ $archive['text'] }}
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    @if ($wireModel)
        <input type="hidden" value="{{ $selected }}" wire:model.live="{{ $wireModel }}" data-dropdown-input>
    @else
        <input type="hidden" value="{{ $selected }}" data-dropdown-input>
    @endif
</div>
