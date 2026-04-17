<?php

namespace App\Livewire\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

class Logout
{
    /**
     * Log the current user out of the application.
     */
    public function __invoke(): void
    {
        // Получаем текущего пользователя и очищаем remember_token
        $user = Auth::guard('web')->user();
        if ($user) {
            $user->setRememberToken(null);
            $user->save();
        }

        Auth::guard('web')->logout();

        Session::invalidate();
        Session::regenerateToken();

        // Удаляем cookie с сохранённым email при выходе
        Cookie::queue(Cookie::forget('remembered_email'));
    }
}
