<?php

namespace App\Services;

use App\Dto\ProfileDto;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

/**
 * Сервис для управления профилем пользователя
 */
class UserService
{
    /**
     * Обновить профиль пользователя
     */
    public function updateProfile(int $userId, ProfileDto $dto): User
    {
        $user = User::findOrFail($userId);
        $user->name = $dto->name;
        $user->email = $dto->email;
        $user->notifications_enabled = $dto->notificationsEnabled;
        $user->save();
        return $user;
    }

    /**
     * Сменить пароль пользователя
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): void
    {
        $user = User::findOrFail($userId);

        if (!Hash::check($currentPassword, $user->password)) {
            throw new \InvalidArgumentException('Текущий пароль указан неверно');
        }

        $user->password = Hash::make($newPassword);
        $user->save();
    }

    /**
     * Проверить возможность смены пароля
     */
    public function canChangePassword(int $userId): bool
    {
        $user = User::findOrFail($userId);
        return !$user->isDemoUser();
    }

    /**
     * Отправить ссылку для сброса пароля
     */
    public function sendPasswordResetLink(string $email): bool
    {
        $status = Password::sendResetLink(['email' => $email]);
        return $status === Password::RESET_LINK_SENT;
    }
}