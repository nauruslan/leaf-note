<?php

namespace App\Http\Controllers;

use App\Models\Safe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class SafePasswordResetController extends Controller
{
    /**
     * Отправить ссылку для сброса пароля сейфа на email пользователя.
     */
    public function sendResetLink(Request $request)
    {
        $user = $request->user();

        $safe = Safe::where('user_id', $user->id)->first();

        if (!$safe || !$safe->hasPassword()) {
            return response()->json([
                'message' => 'Пароль сейфа не установлен'
            ], 400);
        }

        // Создаём зашифрованный идентификатор сейфа
        $safeId = Crypt::encryptString($safe->id);

        // Генерируем подписанный URL
        $signedUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'safe-password.reset',
            now()->addMinutes(60),
            [
                'safe_id' => $safeId,
            ]
        );

        \Illuminate\Support\Facades\Mail::to($user->email)->send(
            new \App\Mail\SafePasswordResetMail($signedUrl, $user->name)
        );

        return response()->json([
            'message' => 'Ссылка для сброса пароля сейфа отправлена на вашу почту'
        ]);
    }

    /**
     * Сбросить пароль сейфа по подписанной ссылке.
     */
    public function reset(Request $request)
    {
        // Проверяем подпись URL
        if (!$request->hasValidSignature()) {
            return response()->json([
                'message' => 'Ссылка недействительна или устарела'
            ], 400);
        }

        $safeId = $request->query('safe_id');

        if (!$safeId) {
            return response()->json([
                'message' => 'Неверный идентификатор сейфа'
            ], 400);
        }

        try {
            $decryptedSafeId = Crypt::decryptString($safeId);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Неверный идентификатор сейфа'
            ], 400);
        }

        $safe = Safe::find($decryptedSafeId);

        if (!$safe) {
            return response()->json([
                'message' => 'Сейф не найден'
            ], 404);
        }

        // Сбрасываем пароль
        $safe->resetPassword();

        // Перенаправляем на главную страницу с флагом успешного сброса
        return redirect('/')->with('safe_password_reset', true);
    }
}