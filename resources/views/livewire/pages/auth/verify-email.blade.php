<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('app', absolute: false), navigate: true);

            return;
        }

        Auth::user()->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div>
    <div class="mb-4 text-sm text-gray-600">
        Спасибо за регистрацию! Прежде чем начать, требуется подтвердить свой адрес электронной почты, нажав на ссылку в
        письме, которое мы только что вам отправили. Если письмо не пришло, мы с удовольствием отправим его повторно.
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600">
            На адрес электронной почты, указанный при регистрации, отправлена новая ссылка для подтверждения.
        </div>
    @endif

    <x-button-save wire:click="sendVerification" height="h-12" target="sendVerification" text="Получить письмо верификации"
        loadingText="Отправка..." class="w-full" />

    <!-- Back to login -->
    <p class="text-center mt-6 text-sm text-gray-600">
        <a wire:click="logout"
            class="font-semibold text-indigo-600 hover:text-indigo-700 transition-colors flex items-center justify-center gap-1 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                </path>
            </svg>
            Назад
        </a>
    </p>
</div>
