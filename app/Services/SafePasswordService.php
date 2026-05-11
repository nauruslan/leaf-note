<?php

namespace App\Services;

use App\Dto\SafePasswordDto;
use App\Models\Safe;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use App\Mail\SafePasswordResetMail;

/**
 * Сервис для управления паролем сейфа
 */
class SafePasswordService
{
    /**
     * Проверить наличие пароля у сейфа
     */
    public function hasPassword(int $userId): bool
    {
        $safe = Safe::where('user_id', $userId)->first();
        return $safe && $safe->hasPassword();
    }

    /**
     * Установить или изменить пароль сейфа
     */
    public function setPassword(int $userId, SafePasswordDto $dto): void
    {
        $safe = Safe::firstOrCreate(['user_id' => $userId]);

        // Если пароль уже установлен - проверяем текущий
        if ($safe->hasPassword()) {
            if (!$safe->verifyPassword($dto->currentPassword)) {
                throw new \InvalidArgumentException('Текущий пароль сейфа указан неверно');
            }
        }

        $safe->setPassword($dto->password);
    }

    /**
     * Отправить ссылку для сброса пароля сейфа
     */
    public function sendResetLink(int $userId): string
    {
        $safe = Safe::where('user_id', $userId)->first();

        if (!$safe || !$safe->hasPassword()) {
            throw new \InvalidArgumentException('Пароль сейфа не установлен');
        }

        $safeId = Crypt::encryptString($safe->id);
        $resetUrl = URL::temporarySignedRoute(
            'safe-password.reset',
            now()->addMinutes(60),
            ['safe_id' => $safeId]
        );

        $user = User::findOrFail($userId);
        Mail::to($user->email)->send(new SafePasswordResetMail($resetUrl, $user->name));

        return $resetUrl;
    }
}