<?php

namespace App\Services;

use App\Models\Safe;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class SafeAuthService
{
    /**
     * Результат аутентификации сейфа.
     */
    public readonly bool $success;
    public readonly ?string $errorMessage;
    public readonly bool $shouldLogout;
    public readonly int $remainingAttempts;

    /**
     * Создать результат успешной аутентификации.
     */
    public static function success(): self
    {
        $result = new self();
        $result->success = true;
        $result->errorMessage = null;
        $result->shouldLogout = false;
        $result->remainingAttempts = 0;

        return $result;
    }

    /**
     * Создать результат неудачной аутентификации.
     */
    public static function failure(string $errorMessage, int $remainingAttempts, bool $shouldLogout = false): self
    {
        $result = new self();
        $result->success = false;
        $result->errorMessage = $errorMessage;
        $result->shouldLogout = $shouldLogout;
        $result->remainingAttempts = $remainingAttempts;

        return $result;
    }

    /**
     * Проверить пароль сейфа.
     */
    public function verifyPassword(int $userId, string $password): self
    {
        $safe = Safe::where('user_id', $userId)->first();

        if (!$safe) {
            return self::failure('Сейф не найден', 0);
        }

        if ($safe->verifyPassword($password)) {
            $safe->recordSuccessfulAccess();
            StateManager::set('safe_unlocked', true);

            return self::success();
        }

        // Неверный пароль - увеличиваем счётчик попыток
        $safe->recordFailedAttempt();

        // Перезагружаем сейф для актуальных данных
        $safe = Safe::where('user_id', $userId)->first();

        // Проверяем, достигнут ли лимит попыток
        if ($safe->hasReachedAttemptLimit()) {
            return self::failure(
                'Лимит попыток превышен',
                0,
                shouldLogout: true
            );
        }

        $remainingAttempts = $safe->max_attempts - $safe->failed_attempts;

        // Особый текст для последней попытки
        if ($remainingAttempts === 1) {
            $errorMessage = 'У вас осталась последняя попытка. В случае ввода неверного пароля будет выполнен выход из аккаунта.';
        } else {
            $errorMessage = "Неверный пароль. Осталось попыток: {$remainingAttempts}";
        }

        return self::failure($errorMessage, $remainingAttempts);
    }

    /**
     * Выполнить выход из аккаунта из-за блокировки сейфа.
     */
    public function performLogoutDueToLockout(int $userId): void
    {
        // Сначала сбрасываем состояние сейфа и счётчик попыток
        $safe = Safe::where('user_id', $userId)->first();
        if ($safe) {
            $safe->unlock(); // сбрасывает failed_attempts и locked_until
        }
        StateManager::remove('safe_unlocked');

        // Выполняем выход пользователя
        Auth::logout();

        // Инвалидируем сессию
        Session::invalidate();
        Session::regenerateToken();
    }

    /**
     * Проверить состояние сейфа при загрузке.
     */
    public function checkSafeState(int $userId): array
    {
        $safe = Safe::where('user_id', $userId)->first();

        // Если пароль не установлен - показываем предупреждение
        if (!$safe || !$safe->hasPassword()) {
            return [
                'isUnlocked' => true,
                'confirmingPassword' => false,
                'showUnprotectedModal' => true,
                'errorMessage' => null,
            ];
        }

        // Проверяем, разблокирован ли сейф через сессию
        $safeUnlocked = StateManager::get('safe_unlocked', false);
        if ($safeUnlocked) {
            return [
                'isUnlocked' => true,
                'confirmingPassword' => false,
                'showUnprotectedModal' => false,
                'errorMessage' => null,
            ];
        }

        // Проверяем и сбрасываем попытки, если прошло более 10 минут
        $safe->checkAndResetAttempts();

        // Сейф заблокирован по попыткам - выходим из аккаунта
        if ($safe->hasReachedAttemptLimit()) {
            $this->performLogoutDueToLockout($userId);
            return [
                'isUnlocked' => false,
                'confirmingPassword' => true,
                'showUnprotectedModal' => false,
                'errorMessage' => null,
                'shouldRedirect' => true,
            ];
        }

        // Требуется ввод пароля
        $errorMessage = null;
        if ($safe->failed_attempts > 0) {
            $remainingAttempts = $safe->max_attempts - $safe->failed_attempts;

            if ($remainingAttempts === 1) {
                $errorMessage = 'У вас осталась последняя попытка. В случае ввода неверного пароля будет выполнен выход из аккаунта.';
            } else {
                $errorMessage = "Неверный пароль. Осталось попыток: {$remainingAttempts}";
            }
        }

        return [
            'isUnlocked' => false,
            'confirmingPassword' => true,
            'showUnprotectedModal' => false,
            'errorMessage' => $errorMessage,
        ];
    }

    /**
     * Получить Safe пользователя.
     */
    public function getUserSafe(int $userId): ?Safe
    {
        return Safe::where('user_id', $userId)->first();
    }

    /**
     * Проверить и сбросить попытки (для wire:poll).
     */
    public function checkAndResetAttempts(int $userId): ?string
    {
        $safe = Safe::where('user_id', $userId)->first();
        if ($safe && $safe->failed_attempts > 0) {
            $safe->checkAndResetAttempts();

            // Если попытки были сброшены, возвращаем null для очистки сообщения
            if ($safe->failed_attempts === 0) {
                return null;
            }
        }

        return null;
    }

    /**
     * Проверить, нужно ли открывать модальное окно пароля при навигации
     */
    public function shouldOpenPasswordModal(int $userId): bool
    {
        $safe = Safe::where('user_id', $userId)->first();
        return $safe && $safe->hasPassword();
    }
}