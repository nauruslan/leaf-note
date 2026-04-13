<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        if ($user && $user->isDemoUser() && $user->isDemoExpired()) {
            // Удаляем истёкшего демо-пользователя
            Auth::logout();
            $user->delete();

            session()->invalidate();
            session()->regenerateToken();

            return redirect()->route('login')
                ->with('status', 'Время демо-доступа истекло. Аккаунт был автоматически удалён.');
        }

        return $next($request);
    }
}