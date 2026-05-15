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

    // Определяем поле для проверки ошибок валидации
    $validationField = $field ?? $wireModel;

    // Если $error явно передан как true - показываем ошибку
    // В противном случае (false или null) проверяем ошибки валидации
    $hasError = $error === true ? true : ($validationField ? $errors->has($validationField) : false);
    $hasIcon = $slot->isNotEmpty();
    $paddingLeft = $hasIcon ? 'pl-10' : 'px-4';
@endphp

<div>
    @if ($label)
        <label @if ($for) for="{{ $for }}" @endif
            class="block {{ $labelSize }} font-medium text-gray-700 mb-2">
            {{ $label }}
        </label>
    @endif
    <div class="relative">
        @if ($hasIcon)
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                {{ $slot }}
            </div>
        @endif
        <input type="{{ $type }}" @if ($id) id="{{ $id }}" @endif
            @if ($wireModel) wire:model="{{ $wireModel }}" @endif
            @if ($autofocus) autofocus @endif @if ($disabled) disabled @endif
            @if ($readonly) readonly @endif
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            class="w-full {{ $paddingLeft }} pr-4 py-3 border {{ $hasError ? 'border-red-500' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all {{ $disabled ? 'bg-gray-50 text-gray-600 cursor-not-allowed' : '' }} {{ $heightClass }}">
    </div>
    @error($validationField)
        <span class="text-red-500 text-sm mt-1 inline-block">{{ $message }}</span>
    @enderror
</div>
