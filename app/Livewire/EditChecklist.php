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

    public ?int $pendingFolderId = null;
    public ?string $pendingColor = null;

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
        'saveChecklist' => 'triggerSave',
        'editorContent' => 'setContent',
        'openChecklist' => 'openChecklist',
        'navigateTo' => 'handleNavigateTo',
        'checklistLoaded' => 'handleChecklistLoaded',
    ];

    public function handleChecklistLoaded(): void
    {
        // Пустой метод для обработки события checklistLoaded
    }

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

    public function save(): void
    {
        $this->js('localStorage.clear()');

        $this->dispatch('saveChecklist',
            folderId: $this->folderId,
            color: $this->color
        );
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

    public function triggerSave($folderId = null, $color = null): void
    {
        $this->pendingFolderId = $folderId ?? $this->folderId;
        $this->pendingColor = $color ?? $this->color;
        $this->dispatch('getEditorContent');
    }

    public function setContent($content): void
    {
        $this->content = $content;
        $this->performSave();
    }

    private function performSave(): void
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
            $this->checklist->color = $this->pendingColor ?? $this->checklist->color;

            if ($this->pendingFolderId !== null) {
                $this->checklist->folder_id = $this->pendingFolderId;
            }

            $this->checklist->save();

            $this->dispatch('checklistUpdated');
            $this->dispatch('navigateTo', 'dashboard');

        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('showError', 'Не удалось сохранить список');
        }
    }

    public function render()
    {
        return view('livewire.edit-checklist');
    }
}
