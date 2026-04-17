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
        <div class="mb-2">
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
                <input type="email" id="email" wire:model="email"
                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                    placeholder="Введите ваш email" required autofocus />
            </div>
            @error('email')
                <span class="text-red-500 text-sm mt-1 inline-block">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" wire:loading.attr="disabled"
            class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold py-3 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform disabled:opacity-50 disabled:cursor-not-allowed">
            <span wire:loading.remove>Отправить ссылку для сброса</span>
            <span wire:loading class="flex items-center justify-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                Отправка...
            </span>
        </button>
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
