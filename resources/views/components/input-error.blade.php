@props(['field'])

@error($field)
    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
@enderror
