<?php

namespace App\Livewire;

use App\Models\Note;
use App\Services\NoteQueryService;
use App\Services\SafeAuthService;
use App\Services\StateManager;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Session;

class SafeSection extends Base
{
    public ?int $safeId = null;

    public string $heading = 'Сейф';
    public string $subheading = 'Защищённые заметки';
    public string $section = 'safe-section';
    public bool $confirmingPassword = false;

    #[Rule('required|min:1')]
    public string $password = '';

    public bool $isUnlocked = false;
    public ?string $errorMessage = null;
    public bool $showUnprotectedModal = false;
    public int $attemptResetPollInterval;

    // UI State для загрузчика
    public bool $isLoading = false;

    #[Session]
    public ?bool $safePasswordReset = null;

    public function mount(): void
    {
        $this->attemptResetPollInterval = \App\Models\Safe::getAttemptResetPollInterval();

        // Проверяем, был ли сброшен пароль сейфа через email
        if ($this->safePasswordReset) {
            $this->safePasswordReset = null;
            $this->dispatch('notification', ['title' => 'Успешно', 'content' => 'Пароль сейфа сброшен. Сейф теперь открыт без защиты.', 'type' => 'success']);
        }

        $this->loadSafe();

        // Устанавливаем safeId для предустановки при создании заметок
        $safe = app(SafeAuthService::class)->getUserSafe(Auth::id());
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
    #[Computed]
    public function getTotalCount(): int
    {
        return app(NoteQueryService::class)->getSafeNotesCount(Auth::id());
    }

    /**
     * Получить актуальную модель Safe из БД.
     * persist: false - не кешируется между запросами, данные всегда актуальны.
     */
    #[Computed(persist: false)]
    public function safe(): ?\App\Models\Safe
    {
        return app(SafeAuthService::class)->getUserSafe(Auth::id());
    }

    /**
     * Достигнут ли лимит попыток ввода пароля?
     */
    #[Computed(persist: false)]
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
        $safe = app(SafeAuthService::class)->getUserSafe(Auth::id());
        if ($safe) {
            $query->where('safe_id', $safe->id);
        } else {
            $query->whereRaw('1 = 0'); // нет safe = нет заметок
        }

        return $query;
    }

    protected function loadSafe(): void
    {
        $state = app(SafeAuthService::class)->checkSafeState(Auth::id());

        $this->isUnlocked = $state['isUnlocked'];
        $this->confirmingPassword = $state['confirmingPassword'];
        $this->showUnprotectedModal = $state['showUnprotectedModal'] ?? false;
        $this->errorMessage = $state['errorMessage'];

        if ($state['shouldRedirect'] ?? false) {
            redirect()->route('login');
        }
    }

    public function verifyPassword(): void
    {
        $this->validate();

        $result = app(SafeAuthService::class)->verifyPassword(Auth::id(), $this->password);

        if ($result->success) {
            // Показываем загрузчик после успешной валидации
            $this->isLoading = true;
            $this->isUnlocked = true;
            $this->confirmingPassword = false;
            $this->password = '';
            $this->errorMessage = null;

            $this->dispatch('finishSafeLoading');
            return;
        }

        $this->password = '';
        $this->errorMessage = $result->errorMessage;

        if ($result->shouldLogout) {
            app(SafeAuthService::class)->performLogoutDueToLockout(Auth::id());
            redirect()->route('login');
        }
    }

    /**
     * Завершение загрузки сейфа
     */
    #[On('finishSafeLoading')]
    public function finishSafeLoading(): void
    {
        $this->isLoading = false;
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
        $this->errorMessage = app(SafeAuthService::class)->checkAndResetAttempts(Auth::id());
    }

    public function render()
    {
        return view('livewire.safe');
    }

}
