<?php

namespace App\Livewire;

use App\Models\Folder;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class EditChecklist extends Component
{
    public ?int $checklistId = null;
    public string $title = '';
    public ?int $folderId = null;
    public string $color = 'white';
    public $content = '';
    public $folders = [];
    public ?Note $checklist = null;
    public bool $isLoaded = false;
    private bool $isSaving = false;

    public const COLORS = [
        'black'   => ['label' => 'Черный',   'bg' => 'bg-black',        'border' => 'border-gray-700', 'ring' => 'focus:ring-gray-700',  'hex' => '#000000'],
        'gray'    => ['label' => 'Серый',    'bg' => 'bg-gray-500',     'border' => 'border-gray-600', 'ring' => 'focus:ring-gray-600',  'hex' => '#6b7280'],
        'red'     => ['label' => 'Красный',  'bg' => 'bg-red-500',      'border' => 'border-red-600',  'ring' => 'focus:ring-red-600',   'hex' => '#ef4444'],
        'orange'  => ['label' => 'Оранжевый','bg' => 'bg-orange-500',   'border' => 'border-orange-600','ring' => 'focus:ring-orange-600','hex' => '#f97316'],
        'yellow'  => ['label' => 'Желтый',   'bg' => 'bg-yellow-500',   'border' => 'border-yellow-600','ring' => 'focus:ring-yellow-600','hex' => '#eab308'],
        'green'   => ['label' => 'Зеленый',  'bg' => 'bg-green-500',    'border' => 'border-green-600', 'ring' => 'focus:ring-green-600', 'hex' => '#22c55e'],
        'blue'    => ['label' => 'Синий',    'bg' => 'bg-blue-500',     'border' => 'border-blue-600',  'ring' => 'focus:ring-blue-600',  'hex' => '#3b82f6'],
        'indigo'  => ['label' => 'Индиго',   'bg' => 'bg-indigo-500',   'border' => 'border-indigo-600', 'ring' => 'focus:ring-indigo-600','hex' => '#6366f1'],
        'purple'  => ['label' => 'Фиолетовый','bg' => 'bg-purple-500',  'border' => 'border-purple-600', 'ring' => 'focus:ring-purple-600','hex' => '#8b5cf6'],
        'pink'    => ['label' => 'Розовый',  'bg' => 'bg-pink-500',     'border' => 'border-pink-600',  'ring' => 'focus:ring-pink-600',  'hex' => '#ec4899'],
        'white'   => ['label' => 'Белый',    'bg' => 'bg-white',        'border' => 'border-gray-300', 'ring' => 'focus:ring-gray-400',  'hex' => '#ffffff'],
    ];

    protected $listeners = [
        'updateFolderId' => 'setFolderId',
        'checklistUpdated' => 'onChecklistUpdated',
        'openChecklist' => 'openChecklist',
        'navigateTo' => 'handleNavigateTo',
        'checklistLoaded' => 'handleChecklistLoaded',
        'checklistContentReady' => 'handleContentReady',
    ];

    public function mount(?int $checklistId = null, ?int $folderId = null): void
    {
        // Для edit-checklist folderId используется как checklistId
        $this->checklistId = $checklistId ?? $folderId;
        $this->folders = Folder::forUser(Auth::user())
            ->active()
            ->orderBy('title')
            ->get();

        if ($this->checklistId) {
            $this->loadChecklist();
        }
    }

    public function handleChecklistLoaded(): void
    {
        // Пустой метод для обработки события checklistLoaded
    }

    public function handleContentReady($content): void
    {
        if ($this->isSaving) {
            return;
        }
        $this->isSaving = true;

        $this->content = $content;
        $this->performSave();
    }

    public function handleNavigateTo(string $section, ?int $folderId = null): void
    {
        if ($section === 'checklist' && $folderId) {
            $this->openChecklist($folderId);
        }
    }

    public function openChecklist($checklistId): void
    {
        $this->checklistId = $checklistId;
        $this->loadChecklist();
    }

    public function loadChecklist(): void
    {
        if (!$this->checklistId) {
            return;
        }

        $this->checklist = Note::where('user_id', Auth::id())
            ->find($this->checklistId);

        if ($this->checklist) {
            $this->title = $this->checklist->title;
            $this->folderId = $this->checklist->folder_id;
            $this->color = $this->checklist->color ?? 'white';
            $this->content = $this->checklist->payload;
            $this->isLoaded = true;

            $this->dispatch('checklistLoaded', content: $this->content);
        }
    }

    public function setFolderId($id): void
    {
        $this->folderId = $id;
    }

    public function setContent($content): void
    {
        $this->content = $content;
    }

    public function onChecklistUpdated(): void
    {
        $this->dispatch('navigateTo', 'dashboard');
    }

    public function cancel(): void
    {
        $this->js('localStorage.clear()');
        $this->dispatch('navigateTo', 'dashboard');
    }

    public function confirmDelete(): void
    {
        if (!$this->checklist) {
            $this->dispatch('showError', 'Список не найден');
            return;
        }

        if ($this->checklist->is_favorite) {
            $this->checklist->update(['is_favorite' => false]);
        }

        if ($this->checklist->moveToTrash()) {
            $this->dispatch('checklistUpdated');
            $this->dispatch('navigateTo', 'dashboard');
        } else {
            $this->dispatch('showError', 'Не удалось удалить список');
        }
    }

    public function openDeleteModal(): void
    {
        $this->js('document.getElementById("delete-modal").classList.add("active")');
    }

    public function getColorsProperty(): array
    {
        return self::COLORS;
    }

    public function prepareAndSave()
    {
        $this->js('localStorage.clear()');

        $this->dispatch('getChecklistContent');
    }

    public function performSave()
    {
        if (is_array($this->content) && count($this->content) === 1) {
            $this->content = reset($this->content);
        }

        if (is_string($this->content) && !empty($this->content)) {
            try {
                $decoded = json_decode($this->content, true, 512, JSON_THROW_ON_ERROR);
                if (is_string($decoded)) {
                    $decoded = json_decode($decoded, true, 512, JSON_THROW_ON_ERROR);
                }
                $this->content = $decoded;
            } catch (\JsonException $e) {
                logger()->error('[EditChecklist] JSON decode error', ['error' => $e->getMessage()]);
                $this->content = null;
            }
        } else {
            logger()->warning('[EditChecklist] No content or empty content');
        }

        $this->saveToDatabase();
    }

    private function saveToDatabase(): void
    {
        try {
            $this->validate([
                'title' => 'required|string|max:255',
                'content' => 'required',
            ]);

            if (!$this->checklist) {
                $this->dispatch('showError', 'Список не найден');
                return;
            }

            $this->checklist->title = $this->title;
            $this->checklist->payload = $this->content;
            $this->checklist->color = $this->color ?? $this->checklist->color;

            if ($this->folderId !== null) {
                $this->checklist->folder_id = $this->folderId;
            }

            $this->checklist->save();

            $this->isSaving = false;

            $this->dispatch('checklistUpdated');
            $this->dispatch('navigateTo', 'dashboard');

        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('showError', 'Не удалось сохранить список');
            $this->isSaving = false;
        }
    }

    public function render()
    {
        return view('livewire.edit-checklist');
    }
}