<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoUserService
{
    /**
     * Время жизни демо-аккаунта в минутах.
     * Легко настраиваемый параметр — измените это значение,
     * чтобы увеличить или уменьшить время жизни демо-пользователя.
     * Срок считается от created_at пользователя.
     */
    public const DEMO_LIFETIME_MINUTES = 2;

    /**
     * Создать демо-пользователя и авторизовать его.
     *
     * @return User|null Созданный пользователь или null в случае ошибки
     */
    public function createAndLogin(): ?User
    {
        $demoNumber = $this->getNextDemoNumber();

        $user = User::create([
            'name' => "demoUser{$demoNumber}",
            'email' => "demo{$demoNumber}@leafnote-demo.com",
            'password' => Hash::make(Str::random(32)),
            'is_demo' => true,
            'email_verified_at' => now(),
        ]);

        Auth::login($user);

        // Очищаем состояние навигации от предыдущей сессии,
        // чтобы новый пользователь начинал с чистого дашборда
        StateManager::clear();

        return $user;
    }

    /**
     * Получить следующий порядковый номер для демо-пользователя.
     * Ищет максимальный номер среди существующих демо-пользователей
     * и инкрементирует его.
     */
    protected function getNextDemoNumber(): int
    {
        $lastDemo = User::where('is_demo', true)
            ->where('email', 'like', 'demo%@leafnote-demo.com')
            ->orderByDesc('id')
            ->first();

        if (!$lastDemo) {
            return 1;
        }

        // Извлекаем номер из email: demo123@leafnote-demo.com -> 123
        if (preg_match('/^demo(\d+)@leafnote-demo\.com$/', $lastDemo->email, $matches)) {
            return (int) $matches[1] + 1;
        }

        return 1;
    }

    /**
     * Удалить всех истёкших демо-пользователей.
     * Срок истечения считается по created_at + DEMO_LIFETIME_MINUTES.
     * Благодаря cascadeOnDelete на внешних ключах,
     * все связанные данные (заметки, папки, корзина, архив, сейф)
     * удалятся автоматически.
     *
     * @return int Количество удалённых пользователей
     */
    public function deleteExpiredDemoUsers(): int
    {
        $expirationTime = now()->subMinutes(self::DEMO_LIFETIME_MINUTES);

        $expiredUsers = User::where('is_demo', true)
            ->where('created_at', '<=', $expirationTime)
            ->get();

        $count = 0;

        foreach ($expiredUsers as $user) {
            // Разлогиниваем пользователя, если он текущий
            if (Auth::id() === $user->id) {
                Auth::logout();
                session()->invalidate();
                session()->regenerateToken();
            }

            $user->delete();
            $count++;
        }

        return $count;
    }
}