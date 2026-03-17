@props([
    'id' => 'toggle-' . uniqid(),
    'name' => null,
    'checked' => false,
    'wireModel' => null,
    'wireClick' => null,
    'class' => '',
    'label' => null,
    'disabled' => false,
])

<label class="toggle-switch {{ $class }} @if ($disabled) opacity-50 cursor-not-allowed @endif">
    <input type="checkbox" id="{{ $id }}" name="{{ $name ?? $id }}" {{ $checked ? 'checked' : '' }}
        @if ($wireModel) wire:model="{{ $wireModel }}" @endif
        @if ($wireClick) wire:click="{{ $wireClick }}" @endif
        {{ $attributes->merge(['class' => '']) }} @disabled($disabled) />
    <span class="slider"></span>
    @if ($label)
        <span class="toggle-label ml-2 text-sm text-gray-700">{{ $label }}</span>
    @endif
</label>
