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
            'confirmClass' => 'bg-red-600 hover:bg-red-700 text-white',
            'showCancel' => true,
            'cancelText' => 'Отменить',
            'buttonsAlign' => 'justify-end',
        ],
        'restore' => [
            'icon' => 'rotate-ccw',
            'iconBg' => 'bg-indigo-100',
            'iconColor' => 'text-indigo-600',
            'confirmText' => 'Восстановить',
            'confirmClass' =>
                'bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white shadow-md hover:shadow-lg',
            'showCancel' => true,
            'cancelText' => 'Отменить',
            'buttonsAlign' => 'justify-end',
        ],
        'confirm' => [
            'icon' => $icon ?? 'help-circle',
            'iconBg' => 'bg-indigo-100',
            'iconColor' => 'text-indigo-600',
            'confirmText' => $confirmText,
            'confirmClass' =>
                'bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white shadow-md hover:shadow-lg',
            'showCancel' => true,
            'cancelText' => 'Отмена',
            'buttonsAlign' => 'justify-end',
        ],
        default => [
            'icon' => $icon ?? 'info',
            'iconBg' => 'bg-indigo-100',
            'iconColor' => 'text-indigo-600',
            'confirmText' => 'Ок',
            'confirmClass' =>
                'bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white shadow-md hover:shadow-lg',
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
                        <button type="button"
                            class="px-5 py-2.5 text-gray-700 font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors"
                            wire:click="{{ $cancelMethod }}">
                            {{ $config['cancelText'] }}
                        </button>
                    @endif

                    <button type="button"
                        class="px-5 py-2.5 {{ $config['confirmClass'] }} font-medium rounded-lg transition-all flex items-center gap-2"
                        wire:click="{{ $confirmMethod }}">
                        {{ $config['confirmText'] }}
                    </button>
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
