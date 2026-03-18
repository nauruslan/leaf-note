@props(['note'])

@php
    $progress = $note->getChecklistProgress();
    $percentage = $progress['percentage'];
    $completed = $progress['completed'];
    $total = $progress['total'];
    $color = $progress['color'];
    // Длина окружности: 2 * π * r = 2 * 3.14159 * 45 ≈ 283
    $PI = 3.14159;
    $radius = 45;
    $circumference = 2 * $PI * $radius;
    $offset = $circumference - ($percentage / 100) * $circumference;
@endphp

<div class="text-center">
    <h4 class="mb-4 text-center font-medium text-gray-700">Прогресс выполнения</h4>
    <div class="flex items-center gap-4 justify-center">
        <!-- Progress Circle Container -->
        <div class="relative w-[100px] h-[100px]">
            <svg viewBox="0 0 100 100" class="w-full h-full" style="transform: rotate(-90deg); display: block;">
                <!-- Background Circle -->
                <circle cx="50" cy="50" r="45" class="fill-none stroke-gray-200" stroke-width="8"
                    stroke-linecap="round" />
                <!-- Progress Circle -->
                @if ($percentage > 0)
                    <circle cx="50" cy="50" r="45" class="fill-none transition-all duration-500"
                        stroke-width="8" stroke-linecap="round" stroke-dasharray="283"
                        stroke-dashoffset="{{ number_format($offset, 1, '.', '') }}" stroke="{{ $color }}" />
                @endif
            </svg>
            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-center">
                <span class="font-bold text-xl text-gray-900">{{ $percentage }}%</span>
            </div>
        </div>
        <div class="max-w-[120px] text-center">
            <p class="text-sm text-gray-600">{{ $completed }} из {{ $total }} задач
                выполнено</p>
        </div>
    </div>
</div>
