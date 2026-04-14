<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class GoogleController
{
    /**
     * Перенаправление на Google для аутентификации.
     */
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Обработка callback от Google.
     * Если пользователь с таким google_id найден — логиним.
     * Если пользователь с таким email найден — привязываем google_id и логиним.
     * Иначе — создаём нового пользователя и логиним.
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('status', 'Не удалось войти через Google. Попробуйте ещё раз.');
        }

        $googleId = $googleUser->getId();
        $email = $googleUser->getEmail();
        $name = $googleUser->getName() ?? 'Пользователь';

        // 1. Ищем пользователя по google_id
        $user = User::where('google_id', $googleId)->first();

        if ($user) {
            Auth::login($user);
            session()->regenerate();

            return redirect()->intended(route('app', absolute: false));
        }

        // 2. Ищем пользователя по email — привязываем Google-аккаунт
        $user = User::where('email', $email)->first();

        if ($user) {
            $user->update([
                'google_id' => $googleId,
                'email_verified_at' => $user->email_verified_at ?? now(),
            ]);

            Auth::login($user);
            session()->regenerate();

            return redirect()->intended(route('app', absolute: false));
        }

        // 3. Создаём нового пользователя
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'google_id' => $googleId,
            'password' => Hash::make(str()->password(32)),
            'email_verified_at' => now(),
        ]);

        event(new Registered($user));

        Auth::login($user);
        session()->regenerate();

        return redirect()->intended(route('app', absolute: false));
    }
}
