<?php

namespace App\Livewire;

use App\Livewire\Traits\WithComponentPagination;
use App\Livewire\Traits\WithFiltering;
use App\Livewire\Traits\WithNoteCreating;
use App\Livewire\Traits\WithNoteOpening;
use App\Livewire\Traits\WithSearch;
use App\Models\Note;
use App\Models\Safe;
use App\Services\StateManager;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class SafeView extends Component
{
    use WithComponentPagination;
    use WithSearch;
    use WithFiltering;
    use WithNoteCreating;
    use WithNoteOpening;

    public $heading = 'Сейф';
    public $subheading = 'Защищённые заметки';
    public $section = 'safe';

    public bool $confirmingPassword = false;
    public string $password = '';
    public bool $isUnlocked = false;
    public ?string $errorMessage = null;
    public ?Safe $safe = null;
    public bool $showUnprotectedModal = false;

    public function mount(): void
    {
        $this->loadSafe();
    }

    protected function loadSafe(): void
    {
        $this->safe = Safe::where('user_id', Auth::id())->first();

        // Если пароль не установлен - показываем предупреждение
        if (!$this->safe || !$this->safe->hasPassword()) {
            $this->showUnprotectedModal = true;
            $this->isUnlocked = true;
            $this->confirmingPassword = false;
            return;
        }

        // Проверяем, разблокирован ли сейф через сессию
        $safeUnlocked = StateManager::get('safe_unlocked', false);
        if ($safeUnlocked) {
            // Сейф уже разблокирован (пользователь ввёл пароль ранее и не покидал safe-контекст)
            $this->isUnlocked = true;
            $this->confirmingPassword = false;
            $this->errorMessage = null;
            return;
        }

        // Если сейф заблокирован
        if ($this->safe->isLocked()) {
            $this->confirmingPassword = true;
            $this->isUnlocked = false;
            $this->errorMessage = "Сейф заблокирован. Попробуйте через {$this->safe->seconds_until_unlock} секунд.";
            return;
        }

        // Сейф заблокирован по попыткам
        if ($this->safe->hasReachedAttemptLimit()) {
            $this->confirmingPassword = true;
            $this->isUnlocked = false;
            $this->errorMessage = 'Слишком много попыток. Сейф заблокирован на 5 минут.';
            return;
        }

        // Требуется ввод пароля
        $this->confirmingPassword = true;
        $this->isUnlocked = false;
    }

    public function verifyPassword(): void
    {
        $this->errorMessage = null;

        if (empty($this->password)) {
            $this->errorMessage = 'Введите пароль';
            return;
        }

        if (!$this->safe) {
            $this->errorMessage = 'Сейф не найден';
            return;
        }

        if ($this->safe->isLocked()) {
            $this->errorMessage = "Сейф заблокирован. Попробуйте через {$this->safe->seconds_until_unlock} секунд.";
            return;
        }

        if ($this->safe->verifyPassword($this->password)) {
            $this->safe->recordSuccessfulAccess();
            StateManager::set('safe_unlocked', true);
            $this->isUnlocked = true;
            $this->confirmingPassword = false;
            $this->password = '';
            $this->errorMessage = null;
            return;
        }

        // Неверный пароль
        $this->safe->recordFailedAttempt();
        $this->password = '';

        if ($this->safe->isLocked()) {
            $this->errorMessage = "Сейф заблокирован на 5 минут из-за многочисленных попыток.";
        } else {
            $remainingAttempts = $this->safe->max_attempts - $this->safe->failed_attempts;
            $this->errorMessage = "Неверный пароль. Осталось попыток: {$remainingAttempts}";
        }
    }

    public function lock(): void
    {
        $this->isUnlocked = false;
        $this->confirmingPassword = true;
    }

    public function closeModal(): void
    {
        $this->showUnprotectedModal = false;
    }

    #[Computed]
    public function notes(): LengthAwarePaginator
    {
        $query = Note::where('user_id', Auth::id())
            ->whereNotNull('safe_id')
            ->with('folder');

        $filterMap = [
            'notes' => ['column' => 'type', 'value' => Note::TYPE_NOTE],
            'checklists' => ['column' => 'type', 'value' => Note::TYPE_CHECKLIST],
        ];
        $query = $this->applyFilter($query, 'type', $filterMap);

        $query = $this->applySorting($query);

        $query = $this->applySearch($query, ['title', 'payload']);

        return $query->paginate($this->perPage, ['*'], 'page', $this->page);
    }


    public function render()
    {
        return view('livewire.safe');
    }
}
