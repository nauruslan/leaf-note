<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public LoginForm $form;

    #[Js]
    public function getRememberValue(): bool
    {
        return $this->form->remember;
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
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

    <!-- Login Form -->
    <form wire:submit.prevent="login" class="space-y-6">
        <!-- Email Field -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                Email
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                        </path>
                    </svg>
                </div>
                <input type="email" id="email" wire:model="form.email"
                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                    placeholder="Введите ваш email" />
            </div>
            @error('form.email')
                <span class="text-red-500 text-sm mt-1 inline-block">{{ $message }}</span>
            @enderror
        </div>

        <!-- Password Field -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                Пароль
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                        </path>
                    </svg>
                </div>
                <input type="password" id="password" wire:model="form.password"
                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                    placeholder="Введите ваш пароль" />
            </div>
            @error('form.password')
                <span class="text-red-500 text-sm mt-1 inline-block">{{ $message }}</span>
            @enderror
        </div>

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

            <a href="{{ route('password.request') }}"
                class="text-sm font-medium text-indigo-600 hover:text-indigo-700 transition-colors">
                Забыли пароль?
            </a>
        </div>

        <!-- Submit Button -->
        <button type="submit" wire:loading.attr="disabled"
            class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold py-3 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform  disabled:opacity-50 disabled:cursor-not-allowed">
            <span wire:loading.remove>Войти в аккаунт</span>
            <span wire:loading class="flex items-center justify-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                Обработка...
            </span>
        </button>
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
        <button wire:click="loginAsDemo"
            class="w-full py-3 px-4 border border-gray-300 rounded-lg font-medium text-gray-700 bg-gray-50 hover:bg-gray-100 transition-all flex items-center justify-center gap-2">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                </path>
            </svg>
            Войти как демо-пользователь
        </button>

        <button
            class="w-full py-3 px-4 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 transition-all flex items-center justify-center gap-2">
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
            Продолжить с Google
        </button>
    </div>

    <!-- Register Link -->
    <p class="text-center mt-6 text-sm text-gray-600">
        Нет аккаунта?
        <a href="{{ route('register') }}" class="font-semibold text-indigo-600 hover:text-indigo-700 transition-colors">
            Зарегистрироваться
        </a>
    </p>

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
    });
</script>
