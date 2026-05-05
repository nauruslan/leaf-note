<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public string $email = '';

    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $status = Password::sendResetLink($this->only('email'));

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }
}; ?>

<div>
    <!-- Header -->
    <div class="text-center mb-4">
        <div class="flex items-center justify-center gap-2 mb-3">
            <span
                class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                LeafNote
            </span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mb-4">Забыли пароль?</h1>
        <p class="text-sm text-gray-600">
            Введите ваш email, и мы отправим ссылку для сброса пароля
        </p>
    </div>

    @if (session('status'))
        <div class="mb-4 p-3 bg-green-50 border border-green-300 rounded-lg text-sm text-green-800">
            Письмо со ссылкой для сброса пароля отправлено на вашу почту.
        </div>
    @endif

    <form wire:submit="sendPasswordResetLink" class="space-y-6">
        <!-- Email Address -->
        <x-input-group label="Email" for="email" type="email" id="email" wireModel="email" field="email"
            height="48px" placeholder="Введите ваш email" :autofocus="true">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                </path>
            </svg>
        </x-input-group>

        <x-button-save type="submit" target="sendPasswordResetLink" text="Отправить ссылку для сброса"
            loadingText="Отправка..." height="h-12" class="w-full shadow-lg hover:shadow-xl" />
    </form>

    <!-- Back to login -->
    <p class="text-center mt-6 text-sm text-gray-600">
        <a href="{{ route('login') }}"
            class="font-semibold text-indigo-600 hover:text-indigo-700 transition-colors flex items-center justify-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                </path>
            </svg>
            Вернуться к входу
        </a>
    </p>
</div>
