<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    #[Locked]
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(string $token): void
    {
        $this->token = $token;

        $this->email = request()->string('email');
    }

    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset($this->only('email', 'password', 'password_confirmation', 'token'), function ($user) {
            $user
                ->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])
                ->save();

            event(new PasswordReset($user));
        });

        if ($status != Password::PASSWORD_RESET) {
            $this->addError('email', __($status));

            return;
        }

        Session::flash('status', __($status));

        $this->redirectRoute('login');
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
        <h1 class="text-2xl font-bold text-gray-900">Установка нового пароля</h1>
    </div>

    @if (session('status'))
        <div class="mb-4 p-3 bg-green-50 border border-green-300 rounded-lg text-sm text-green-800">
            Ваш пароль был успешно сброшен.
        </div>
    @endif

    <form wire:submit="resetPassword" class="space-y-4">
        <!-- Email Address -->
        <x-input-group label="Email" for="email" type="email" id="email" wireModel="email" height="48px"
            field="email" placeholder="Введите ваш email">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                </path>
            </svg>
        </x-input-group>

        <!-- Password -->
        <x-input-group label="Новый пароль" for="password" type="password" id="password" wireModel="password"
            height="48px" field="password" placeholder="Введите новый пароль">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                </path>
            </svg>
        </x-input-group>

        <!-- Confirm Password -->
        <x-input-group label="Подтвердите пароль" for="password_confirmation" type="password" id="password_confirmation"
            wireModel="password_confirmation" height="48px" field="password_confirmation"
            placeholder="Подтвердите новый пароль">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                </path>
            </svg>
        </x-input-group>

        <x-button-save type="submit" target="resetPassword" text="Сохранить новый пароль" loadingText="Сохранение..."
            height="h-12" class="w-full shadow-lg hover:shadow-xl" />
    </form>

    <!-- Back to login -->
    <p class="text-center mt-6 text-sm text-gray-600">
        <a href="{{ route('login') }}"
            class="font-semibold text-indigo-600 hover:text-indigo-700 transition-colors flex items-center justify-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                </path>
            </svg>
            Вернуться
        </a>
    </p>
</div>
