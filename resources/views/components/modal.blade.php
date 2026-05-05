@props([
    'type' => 'info',
    'show' => false,
    'title' => '',
    'description' => '',
    'icon' => null,
    'confirmMethod' => '',
    'cancelMethod' => '',
    'confirmText' => 'Подтвердить',
])

@php
    $config = match ($type) {
        'delete' => [
            'icon' => 'trash-2',
            'iconBg' => 'bg-red-100',
            'iconColor' => 'text-red-600',
            'confirmText' => 'Удалить',
            'variant' => 'danger',
            'showCancel' => true,
            'cancelText' => 'Отменить',
            'buttonsAlign' => 'justify-end',
        ],
        'restore' => [
            'icon' => 'help-circle',
            'iconBg' => 'bg-indigo-100',
            'iconColor' => 'text-indigo-600',
            'confirmText' => 'Восстановить',
            'variant' => 'default',
            'showCancel' => true,
            'cancelText' => 'Отменить',
            'buttonsAlign' => 'justify-end',
        ],
        'confirm' => [
            'icon' => $icon ?? 'help-circle',
            'iconBg' => 'bg-indigo-100',
            'iconColor' => 'text-indigo-600',
            'confirmText' => $confirmText,
            'variant' => 'default',
            'showCancel' => true,
            'cancelText' => 'Отмена',
            'buttonsAlign' => 'justify-end',
        ],
        default => [
            'icon' => $icon ?? 'info',
            'iconBg' => 'bg-indigo-100',
            'iconColor' => 'text-indigo-600',
            'confirmText' => 'Ок',
            'variant' => 'default',
            'showCancel' => false,
            'cancelText' => '',
            'buttonsAlign' => 'justify-center',
        ],
    };
@endphp

@if ($show)
    <div class="fixed inset-0 z-50" data-modal-wrapper>
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" wire:click="{{ $cancelMethod }}"></div>

        <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6 pointer-events-auto">
                <div class="flex items-start gap-4">
                    <div
                        class="inline-flex items-center justify-center w-16 h-16 rounded-full {{ $config['iconBg'] }} {{ $config['iconColor'] }} mb-2 shrink-0">
                        <i data-lucide="{{ $config['icon'] }}" class="w-8 h-8"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-xl font-bold text-gray-900">{{ $title }}</h3>
                        @if ($description)
                            <p class="text-gray-600 mt-2">{{ $description }}</p>
                        @endif
                    </div>
                </div>

                @if (isset($content))
                    <div class="mt-4">{{ $content }}</div>
                @endif

                <div class="flex gap-4 mt-6 {{ $config['buttonsAlign'] }}">
                    @if ($config['showCancel'])
                        <x-secondary-button wire:click="{{ $cancelMethod }}">
                            {{ $config['cancelText'] }}
                        </x-secondary-button>
                    @endif

                    <x-primary-button height="h-10" :variant="$config['variant']" wire:click="{{ $confirmMethod }}">
                        {{ $config['confirmText'] }}
                    </x-primary-button>
                </div>
            </div>
        </div>
    </div>
@endif

@script
    <script>
        // Наблюдатель за DOM — добавляет/удаляет класс modal-open на html
        const observer = new MutationObserver(() => {
            const hasModal = document.querySelector('[data-modal-wrapper]');
            const html = document.documentElement;

            if (hasModal && !html.classList.contains('modal-open')) {
                html.classList.add('modal-open');
            } else if (!hasModal && html.classList.contains('modal-open')) {
                html.classList.remove('modal-open');
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    </script>
@endscript
