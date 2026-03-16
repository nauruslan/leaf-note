@props(['active' => false, 'size' => '40px'])

<button class="favorite-btn {{ $active ? 'active' : '' }}" type="button"
    {{ $attributes->merge(['aria-label' => 'Добавить в избранное']) }}
    style="{{ $size !== '40px' ? 'width: ' . $size . '; height: ' . $size . ';' : '' }}">
    <svg viewBox="0 0 24 24" style="width: {{ $size }}; height: {{ $size }};">
        <!-- Заливка -->
        <path class="star-fill"
            d="M12 2.5l2.9 5.88 6.5.95-4.7 4.58 1.1 6.44L12 17.77 6.2 20.35l1.1-6.44-4.7-4.58 6.5-.95L12 2.5z" />

        <!-- Контур -->
        <path class="star-stroke"
            d="M12 2.5l2.9 5.88 6.5.95-4.7 4.58 1.1 6.44L12 17.77 6.2 20.35l1.1-6.44-4.7-4.58 6.5-.95L12 2.5z" />
    </svg>
</button>
