<?php

namespace App\Livewire\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

class Logout
{

    public function __invoke(): void
    {
        // Получаем текущего пользователя
        $user = Auth::guard('web')->user();

        if ($user) {
            // Проверяем, является ли пользователь демо-аккаунтом
            $isDemoUser = $user->isDemoUser();

            // Очищаем remember_token
            $user->setRememberToken(null);
            $user->save();

            // Если это демо-пользователь, удаляем его из базы данных
            if ($isDemoUser) {
                $user->delete();
            }
        }

        Auth::guard('web')->logout();

        Session::invalidate();
        Session::regenerateToken();

        // Удаляем cookie с сохранённым email при выходе
        Cookie::queue(Cookie::forget('remembered_email'));
    }
}
