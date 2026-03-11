<?php

namespace App\Livewire;

use App\Models\Folder;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CreateChecklistView extends Component
{
    public string $title = '';
    public ?int $pendingFolderId = null;
    public string $pendingColor = 'default';
    public ?int $folderId = null;
    public string $color = 'white';
    public $content = '';
    public $folders = [];

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
        'checklistSaved' => 'onChecklistSaved',
        'saveChecklist' => 'triggerSave',
        'editorContent' => 'setContent',
    ];

    public function mount()
    {
        $this->folders = Folder::forUser(Auth::user())
            ->active()
            ->orderBy('title')
            ->get();
    }

    public function setFolderId($id)
    {
        $this->folderId = $id;
    }

    public function save()
    {
        $this->js('localStorage.clear()');

        $this->dispatch('saveChecklist',
            folderId: $this->folderId,
            color: $this->color
        );
    }

    public function onChecklistSaved()
    {
        $this->dispatch('navigateTo', 'dashboard');
    }

    public function cancel()
    {
        $this->js('localStorage.clear()');
        $this->dispatch('deleteUploadedImages');
        $this->dispatch('navigateTo', 'dashboard');
    }

    public function getColorsProperty()
    {
        return self::COLORS;
    }

    public function triggerSave($folderId = null, $color = 'default'): void
    {
        $this->pendingFolderId = $folderId;
        $this->pendingColor = $color;
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

            $note = new Note();
            $note->title = $this->title;
            $note->type = Note::TYPE_CHECKLIST;
            $note->payload = $this->content;
            $note->color = $this->pendingColor ?? 'default';
            $note->user_id = Auth::id();

            if ($this->pendingFolderId) {
                $note->folder_id = $this->pendingFolderId;
            } else {
                $note->archive_id = Auth::user()->archive->id;
            }

            $note->save();

            $this->reset(['title', 'content']);
            $this->pendingFolderId = null;
            $this->pendingColor = 'default';

            $this->js('localStorage.clear()');

            $this->dispatch('checklistSaved');
            $this->dispatch('navigateTo', 'dashboard');

        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('showError', 'Не удалось сохранить список');
        }
    }

    public function render()
    {
        return view('livewire.create-checklist');
    }
}
