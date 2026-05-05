@props([
    'label' => null,
    'for' => null,
    'type' => 'text',
    'id' => null,
    'wireModel' => null,
    'field' => null,
    'error' => null,
    'autofocus' => false,
    'disabled' => false,
    'readonly' => false,
    'placeholder' => null,
    'height' => '40px',
    'labelSize' => 'text-sm',
])

@php
    $heightClass = match ($height) {
        '36px' => 'h-9',
        '40px' => 'h-10',
        '44px' => 'h-11',
        '48px' => 'h-12',
        default => 'h-10',
    };

    $hasError = $error !== null ? (bool) $error : ($field ? $errors->has($field) : false);
@endphp

<div>
    @if ($label)
        <label @if ($for) for="{{ $for }}" @endif
            class="block {{ $labelSize }} font-medium text-gray-700 mb-2">
            {{ $label }}
        </label>
    @endif

    <input type="{{ $type }}" @if ($id) id="{{ $id }}" @endif
        @if ($wireModel) wire:model="{{ $wireModel }}" @endif
        @if ($autofocus) autofocus @endif @if ($disabled) disabled @endif
        @if ($readonly) readonly @endif
        @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        class="w-full border {{ $hasError ? 'border-red-500' : 'border-gray-300' }} rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-gradient-to-r focus:from-indigo-500 focus:to-purple-500 focus:border-gradient-to-r focus:from-indigo-500 focus:to-purple-500 transition-shadow {{ $disabled ? 'bg-gray-50 text-gray-600 cursor-not-allowed' : '' }} {{ $heightClass }}">
    @error($field)
        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
