<?php

use App\Livewire\Forms\LoginForm;
use App\Services\DemoUserService;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public LoginForm $form;
    public bool $isLoading = false;
    public ?string $loadingMethod = null;

    protected $listeners = [
        'startLoading' => 'startLoading',
        'finishLoading' => 'finishLoading',
    ];

    public function mount(): void
    {
        // Восстанавливаем email из cookie, если он был сохранён
        $savedEmail = Cookie::get('remembered_email');
        if ($savedEmail) {
            $this->form->email = $savedEmail;
            $this->form->remember = true;
        }

        // Проверяем сообщение об истечении демо-аккаунта из cookie
        $demoExpiredMessage = Cookie::get('demo_expired_message');
        if ($demoExpiredMessage) {
            session()->flash('demo_expired', $demoExpiredMessage);
            Cookie::queue(Cookie::forget('demo_expired_message'));
        }
    }

    #[Js]
    public function getRememberValue(): bool
    {
        return $this->form->remember;
    }

    public function login(): void
    {
        try {
            $this->validate();

            // Устанавливаем флаг загрузки только после успешной валидации
            $this->isLoading = true;
            $this->loadingMethod = 'login';

            $this->form->authenticate();

            Session::regenerate();

            // Сохраняем или удаляем email в cookie в зависимости от галочки "Запомнить меня"
            if ($this->form->remember) {
                cookie()->queue(cookie('remembered_email', $this->form->email, 43200)); // 30 дней
            } else {
                cookie()->queue(cookie()->forget('remembered_email'));
            }

            $this->redirect(route('app'));
        } catch (\Exception $e) {
            $this->isLoading = false;
            $this->loadingMethod = null;
            throw $e;
        }
    }

    public function startLoading(string $method): void
    {
        $this->isLoading = true;
        $this->loadingMethod = $method;
    }

    public function finishLoading(): void
    {
        $this->isLoading = false;
        $this->loadingMethod = null;
    }

    // Создать демо-пользователя и войти в него.
    public function loginAsDemo(DemoUserService $demoUserService): void
    {
        try {
            // Устанавливаем флаг загрузки
            $this->isLoading = true;
            $this->loadingMethod = 'demo';

            $demoUserService->createAndLogin($this->form->remember);

            Session::regenerate();

            // Сохраняем email в cookie если выбрано "Запомнить меня"
            if ($this->form->remember) {
                cookie()->queue(cookie('remembered_email', 'demo', 43200)); // 30 дней
            }

            $this->redirect(route('app'));
        } catch (\Exception $e) {
            $this->isLoading = false;
            $this->loadingMethod = null;
            throw $e;
        }
    }

    // Перенаправить на Google авторизацию с сохранением remember.
    public function loginWithGoogle(): void
    {
        try {
            // Устанавливаем флаг загрузки
            $this->isLoading = true;
            $this->loadingMethod = 'google';

            // Сохраняем состояние remember в сессию для использования в callback
            session(['google_remember' => $this->form->remember]);

            // Сохраняем email в cookie если выбрано "Запомнить меня"
            if ($this->form->remember) {
                cookie()->queue(cookie('remembered_email', 'google', 43200)); // 30 дней
            }

            $this->redirect(route('auth.google.redirect'));
        } catch (\Exception $e) {
            $this->isLoading = false;
            $this->loadingMethod = null;
            throw $e;
        }
    }
}; ?>
<div class="min-h-[450px] flex flex-col relative" id="login-container">
    <!-- Loader Overlay -->
    <div id="loader-overlay" class="absolute inset-0 bg-white flex items-center justify-center z-50 hidden">
        <div class="flex flex-col items-center">
            <x-loader class="w-20 h-20 text-indigo-600 mb-4" />
            <p class="text-gray-700 font-medium">Вход в систему...</p>
        </div>
    </div>

    <div id="login-content" class="flex-1 flex flex-col">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="flex items-center justify-center gap-2 mb-3">
                <span
                    class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                    LeafNote
                </span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Войдите в свой аккаунт</h1>
        </div>

        @if (session('status'))
            <div class="mb-4 p-3 bg-green-50 border border-green-300 rounded-lg text-sm text-green-800">
                Ваш пароль был успешно сброшен. Вы можете войти с новым паролем.
            </div>
        @endif

        @if (session('demo_expired'))
            <div class="mb-4 p-3 bg-red-50 border border-red-300 rounded-lg text-sm text-red-800">
                {{ session('demo_expired') }}
            </div>
        @endif

        <!-- Login Form -->
        <form wire:submit.prevent="login" class="space-y-4">
            <!-- Email Field -->
            <x-input-group label="Email" for="email" type="email" id="email" wireModel="form.email"
                field="form.email" placeholder="Введите ваш email" height="48px">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                    </path>
                </svg>
            </x-input-group>

            <!-- Password Field -->
            <x-input-group label="Пароль" for="password" type="password" id="password" wireModel="form.password"
                field="form.password" placeholder="Введите ваш пароль" height="48px">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                    </path>
                </svg>
            </x-input-group>

            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between">
                <label class="flex items-center cursor-pointer">
                    <div class="relative mt-1">
                        <input type="checkbox" id="remember-checkbox" wire:model="form.remember" class="sr-only" />
                        <label for="remember-checkbox" class="cursor-pointer">
                            <div class="relative inline-block w-10 h-6">
                                <div id="toggle-bg"
                                    class="block w-full h-full rounded-full transition-colors duration-300 bg-gray-200">
                                </div>
                                <div id="toggle-dot"
                                    class="absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform duration-300">
                                </div>
                            </div>
                        </label>
                    </div>
                    <span class="ml-3 text-sm font-medium text-gray-700">Запомнить меня</span>
                </label>

                <x-link href="{{ route('password.request') }}" class="text-sm">Забыли пароль?</x-link>
            </div>

            <!-- Submit Button -->
            <x-primary-button type="submit" height="h-12" class="w-full" onclick="showLoader()">
                Войти в аккаунт
            </x-primary-button>
        </form>

        <!-- Divider -->
        <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white text-gray-500">или</span>
            </div>
        </div>

        <!-- Demo & Social Login -->
        <div class="space-y-3">
            <x-secondary-button wire:click="loginAsDemo" height="h-12" bgColor="bg-gray-50" class="w-full"
                onclick="showLoader()">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                    </path>
                </svg>
                <span class="ml-2">Войти как демо-пользователь</span>
            </x-secondary-button>

            <x-secondary-button wire:click="loginWithGoogle" height="h-12" bgColor="bg-white" class="w-full"
                onclick="showLoader()">
                <svg class="w-5 h-5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                        fill="#4285F4" />
                    <path
                        d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                        fill="#34A853" />
                    <path
                        d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                        fill="#FBBC05" />
                    <path
                        d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                        fill="#EA4335" />
                </svg>
                <span class="ml-2">Войти через Google</span>
            </x-secondary-button>
        </div>

        <!-- Register Link -->
        <p class="text-center mt-6 text-sm text-gray-600">
            Нет аккаунта?
            <x-link href="{{ route('register') }}">Зарегистрироваться</x-link>
        </p>
    </div>
</div>
</div>

<!-- Custom Toggle JavaScript -->
<script>
    document.addEventListener('livewire:init', () => {
        const checkbox = document.getElementById('remember-checkbox');
        const toggleBg = document.getElementById('toggle-bg');
        const toggleDot = document.getElementById('toggle-dot');

        if (!checkbox || !toggleBg || !toggleDot) return;

        // Initial state from Livewire
        const updateToggleState = () => {
            if (checkbox.checked) {
                toggleBg.classList.remove('bg-gray-200');
                toggleBg.classList.add('bg-indigo-600');
                toggleDot.classList.add('translate-x-4');
            } else {
                toggleBg.classList.remove('bg-indigo-600');
                toggleBg.classList.add('bg-gray-200');
                toggleDot.classList.remove('translate-x-4');
            }
        };

        // Update on page load
        updateToggleState();

        // Update when checkbox changes
        checkbox.addEventListener('change', updateToggleState);

        // Update when Livewire updates (e.g., after validation error)
        Livewire.hook('element.updated', (el, component) => {
            if (el.id === 'remember-checkbox') {
                setTimeout(updateToggleState, 0);
            }
        });

        // Show loader function
        window.showLoader = function() {
            const loaderOverlay = document.getElementById('loader-overlay');
            const loginContent = document.getElementById('login-content');

            if (loaderOverlay) {
                loaderOverlay.classList.remove('hidden');
            }

            if (loginContent) {
                loginContent.classList.add('opacity-50', 'pointer-events-none');
            }
        };

        // Hide loader on validation error
        Livewire.hook('message.failed', (message, component) => {
            const loaderOverlay = document.getElementById('loader-overlay');
            const loginContent = document.getElementById('login-content');

            if (loaderOverlay) {
                loaderOverlay.classList.add('hidden');
            }

            if (loginContent) {
                loginContent.classList.remove('opacity-50', 'pointer-events-none');
            }
        });
    });
</script>
