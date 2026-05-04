@props(['for' => null])

<label @if ($for) for="{{ $for }}" @endif
    class="block text-sm font-medium text-gray-700 mb-2">
    {{ $slot }}
</label>
