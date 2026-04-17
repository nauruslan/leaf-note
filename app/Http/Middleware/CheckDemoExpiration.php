<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware для проверки истечения срока демо-пользователя.
 * Если демо-аккаунт истёк — пользователь удаляется, сессия очищается,
 * происходит редирект на страницу входа с сообщением.
 */
class CheckDemoExpiration
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Проверяем только если пользователь авторизован
        if ($user && $user->isDemoUser() && $user->isDemoExpired()) {
            // Сначала полностью очищаем сессию и авторизацию
            Auth::logout();

            // Удаляем пользователя из базы
            $user->delete();

            // Полностью очищаем сессию
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Удаляем cookie с сохранённым email (демо email больше не существует)
            Cookie::queue(Cookie::forget('remembered_email'));

            // Сохраняем сообщение в cookie (т.к. сессия очищена)
            Cookie::queue(Cookie::make('demo_expired_message', 'Срок действия демо-аккаунта истёк. Аккаунт был автоматически удалён.', 1));

            // Возвращаем редирект
            return redirect()->route('login');
        }

        return $next($request);
    }
}
