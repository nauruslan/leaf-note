<?php

namespace App\Livewire;

use App\Models\Note;
use App\Models\Safe;
use App\Services\StateManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Computed;

class SafeView extends BaseView
{
    public ?int $safeId = null;

    public string $heading = 'Сейф';
    public string $subheading = 'Защищённые заметки';
    public string $section = 'safe';
    public bool $confirmingPassword = false;
    public string $password = '';
    public bool $isUnlocked = false;
    public ?string $errorMessage = null;
    public bool $showUnprotectedModal = false;
    public int $attemptResetPollInterval;

    public function mount(): void
    {
        $this->attemptResetPollInterval = Safe::getAttemptResetPollInterval();

        // Проверяем, был ли сброшен пароль сейфа через email
        if (session()->has('safe_password_reset')) {
            session()->forget('safe_password_reset');
            $this->dispatch('notification', ['title' => 'Успешно', 'content' => 'Пароль сейфа сброшен. Сейф теперь открыт без защиты.', 'type' => 'success']);
        }

        $this->loadSafe();

        // Устанавливаем safeId для предустановки при создании заметок
        $safe = $this->safe();
        if ($safe) {
            $this->safeId = $safe->id;
        }
    }

    protected function getBaseConditions(): array
    {
        return []; // safe_id фильтруется в buildNotesQuery
    }

    /**
     * Количество заметок в сейфе (для отображения в UI).
     */
    #[Computed(cache: true, seconds: 60)]
    protected function getTotalCount(): int
    {
        return Note::forUser(Auth::id())
            ->whereNotNull('safe_id')
            ->count();
    }

    /**
     * Получить актуальную модель Safe из БД.
     * Не используем caching - каждая проверка должна быть актуальной.
     */
    public function safe(): ?Safe
    {
        return Safe::where('user_id', Auth::id())->first();
    }

    /**
     * Достигнут ли лимит попыток ввода пароля?
     */
    public function hasReachedAttemptLimit(): bool
    {
        $safe = $this->safe();
        return $safe && $safe->hasReachedAttemptLimit();
    }

    /**
     * Переопределяем buildNotesQuery для фильтрации по конкретному safe.
     */
    protected function buildNotesQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = Note::forUser(Auth::id())->with('folder');

        // Фильтруем только заметки конкретного сейфа
        $safe = $this->safe();
        if ($safe) {
            $query->where('safe_id', $safe->id);
        } else {
            $query->whereRaw('1 = 0'); // нет safe = нет заметок
        }

        return $query;
    }

    protected function loadSafe(): void
    {
        $safe = $this->safe();

        // Если пароль не установлен - показываем предупреждение
        if (!$safe || !$safe->hasPassword()) {
            $this->showUnprotectedModal = true;
            $this->isUnlocked = true;
            $this->confirmingPassword = false;
            return;
        }

        // Проверяем, разблокирован ли сейф через сессию
        $safeUnlocked = StateManager::get('safe_unlocked', false);
        if ($safeUnlocked) {
            $this->isUnlocked = true;
            $this->confirmingPassword = false;
            $this->errorMessage = null;
            return;
        }

        // Проверяем и сбрасываем попытки, если прошло более 10 минут
        $safe->checkAndResetAttempts();

        // Сейф заблокирован по попыткам - выходим из аккаунта
        if ($safe->hasReachedAttemptLimit()) {
            $this->performLogoutDueToLockout();
            return;
        }

        // Требуется ввод пароля
        $this->confirmingPassword = true;
        $this->isUnlocked = false;

        // Показываем сообщение о количестве оставшихся попыток
        if ($safe->failed_attempts > 0) {
            $remainingAttempts = $safe->max_attempts - $safe->failed_attempts;

            if ($remainingAttempts === 1) {
                $this->errorMessage = 'У вас осталась последняя попытка. В случае ввода неверного пароля будет выполнен выход из аккаунта.';
            } else {
                $this->errorMessage = "Неверный пароль. Осталось попыток: {$remainingAttempts}";
            }
        }
    }

    public function verifyPassword(): void
    {
        $this->errorMessage = null;

        if (empty($this->password)) {
            $this->errorMessage = 'Введите пароль';
            return;
        }

        // Получаем актуальную модель из БД
        $safe = $this->safe();

        if (!$safe) {
            $this->errorMessage = 'Сейф не найден';
            return;
        }

        if ($safe->verifyPassword($this->password)) {
            $safe->recordSuccessfulAccess();
            StateManager::set('safe_unlocked', true);
            $this->isUnlocked = true;
            $this->confirmingPassword = false;
            $this->password = '';
            $this->errorMessage = null;
            return;
        }

        // Неверный пароль - увеличиваем счётчик попыток
        $safe->recordFailedAttempt();
        $this->password = '';

        // Перезагружаем сейф для актуальных данных
        $safe = $this->safe();

        // Проверяем, достигнут ли лимит попыток
        if ($safe->hasReachedAttemptLimit()) {
            // Выполняем выход из аккаунта
            $this->performLogoutDueToLockout();
        } else {
            $remainingAttempts = $safe->max_attempts - $safe->failed_attempts;

            // Особый текст для последней попытки
            if ($remainingAttempts === 1) {
                $this->errorMessage = 'У вас осталась последняя попытка. В случае ввода неверного пароля будет выполнен выход из аккаунта.';
            } else {
                $this->errorMessage = "Неверный пароль. Осталось попыток: {$remainingAttempts}";
            }
        }
    }

    /**
     * Выполнить выход из аккаунта из-за блокировки сейфа.
     */
    protected function performLogoutDueToLockout(): void
    {
        $userId = Auth::id();

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

        // Перенаправляем на страницу входа
        redirect()->route('login');
    }

    public function lock(): void
    {
        $this->isUnlocked = false;
        $this->confirmingPassword = true;
        StateManager::remove('safe_unlocked');
    }

    public function closeModal(): void
    {
        $this->showUnprotectedModal = false;
        $this->dispatch('modalClosed');
    }

    /**
     * Периодическая проверка и сброс попыток (для wire:poll).
     */
    public function checkAttempts(): void
    {
        $safe = $this->safe();
        if ($safe && $safe->failed_attempts > 0) {
            $safe->checkAndResetAttempts();

            // Если попытки были сброшены, обновляем сообщение
            if ($safe->failed_attempts === 0) {
                $this->errorMessage = null;
            }
        }
    }

    public function render()
    {
        return view('livewire.safe');
    }

}