<?php

namespace App\Livewire;

use App\Livewire\Traits\WithComponentPagination;
use App\Livewire\Traits\WithFavorite;
use App\Livewire\Traits\WithFiltering;
use App\Livewire\Traits\WithSearch;
use App\Models\Note;
use App\Models\Safe;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class SafeView extends Component
{
    use WithComponentPagination;
    use WithSearch;
    use WithFiltering;
    use WithFavorite;

    protected $listeners = [
        'openSafePasswordModal' => 'openPasswordModal'
    ];

    public bool $confirmingPassword = false;
    public string $password = '';
    public bool $isUnlocked = false;
    public ?string $errorMessage = null;
    public ?Safe $safe = null;

    public function mount(): void
    {
        $this->loadSafe();
    }

    protected function loadSafe(): void
    {
        $this->safe = Safe::where('user_id', Auth::id())->first();

        // Если пароль не установлен - сейф открыт
        if (!$this->safe || !$this->safe->hasPassword()) {
            $this->isUnlocked = true;
            $this->confirmingPassword = false;
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

    public function openPasswordModal(): void
    {
        $this->loadSafe();

        // Если пароль не установлен - сейф открыт
        if (!$this->safe || !$this->safe->hasPassword()) {
            $this->isUnlocked = true;
            $this->confirmingPassword = false;
            return;
        }

        // Если сейф заблокирован
        if ($this->safe->isLocked()) {
            $this->confirmingPassword = true;
            $this->isUnlocked = false;
            $this->errorMessage = "Сейф заблокирован. Попробуйте через {$this->safe->seconds_until_unlock} секунд.";
            return;
        }

        // Требуется ввод пароля
        $this->confirmingPassword = true;
        $this->isUnlocked = false;
        $this->errorMessage = null;
    }

    public function closeModal(): void
    {
        $this->confirmingPassword = false;
        $this->password = '';
        $this->errorMessage = null;
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

    #[Computed]
    public function notes(): LengthAwarePaginator
    {
        $query = Note::where('user_id', Auth::id())
            ->whereNotNull('safe_id')
            ->with('folder');

        // Применяем фильтр
        $filterMap = [
            'notes' => ['column' => 'type', 'value' => Note::TYPE_NOTE],
            'checklists' => ['column' => 'type', 'value' => Note::TYPE_CHECKLIST],
        ];
        $query = $this->applyFilter($query, 'type', $filterMap);

        // Применяем сортировку
        $query = $this->applySorting($query);

        // Применяем поиск
        $query = $this->applySearch($query, ['title', 'payload']);

        // Пагинация
        return $query->paginate($this->perPage, ['*'], 'page', $this->page);
    }

    /**
     * Сбросить пагинацию при изменении любого из параметров.
     */
    public function updated($property): void
    {
        if (in_array($property, ['search', 'filter', 'sort'])) {
            $this->resetPagination();
        }
    }

    public function createSafeNote(): void
    {
        $this->dispatch('navigateTo', 'create-safe-note');
    }

    public function createNote(): void
    {
        $this->dispatch('navigateTo', 'create-note');
    }

    public function createChecklist(): void
    {
        $this->dispatch('navigateTo', 'create-checklist');
    }

    public function render()
    {
        return view('livewire.safe');
    }
}