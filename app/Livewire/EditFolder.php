<?php

namespace App\Livewire;

use App\Models\Folder;
use App\Services\StateManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class EditFolder extends Component
{
    public $heading = 'Редактирование папки';
    public $section = 'edit-folder';

    public ?int $folderId = null;
    public string $title = '';
    public string $color = '';
    public string $icon = '';
    public bool $confirmingDeletion = false;

    private ?Folder $folder = null;

    public function mount(?int $folderId = null)
    {
        $this->folderId = $folderId;

        if ($this->folderId) {
            $this->folder = Folder::where('user_id', Auth::id())
                ->where('id', $this->folderId)
                ->active()
                ->first();
        }

        if ($this->folder) {
            $this->title = $this->folder->title;
            $this->color = $this->folder->color;
            $this->icon = $this->folder->icon;
        }
    }

    public function getIconsProperty(): array
    {
        return Folder::ICONS;
    }

    /**
     * Получить занятые иконки текущего пользователя (исключая текущую папку)
     */
    public function getUsedIconsProperty(): array
    {
        $query = Folder::where('user_id', Auth::id())
            ->whereNull('trash_id');

        if ($this->folder) {
            $query->where('id', '!=', $this->folder->id);
        }

        return $query->pluck('icon')->toArray();
    }

    public function createFolder()
    {
        $this->save();
    }

    public function save()
    {
        // Если передан folderId, но папка не загружена, попробуем загрузить
        if ($this->folderId && !$this->folder) {
            $this->folder = Folder::where('user_id', Auth::id())
                ->where('id', $this->folderId)
                ->active()
                ->first();

            if (!$this->folder) {
                $this->dispatch('notify', ['message' => 'Папка не найдена или у вас нет прав на её редактирование.', 'type' => 'error']);
                return;
            }
        }

        // Правила валидации
        $rules = [
            'title' => [
                'required',
                'string',
                'min:1',
                'max:12',
            ],
            'color' => [
                'required',
                'string',
                'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            ],
            'icon' => [
                'required',
                'string',
                'in:' . implode(',', array_keys(Folder::ICONS)),
            ],
        ];

        // Динамически добавляем правило unique для title с игнорированием ID редактируемой папки
        $titleUniqueRule = Rule::unique('folders')
            ->where('user_id', Auth::id())
            ->whereNull('trash_id');

        if ($this->folder) {
            $titleUniqueRule->ignore($this->folder->id);
        }

        $rules['title'][] = $titleUniqueRule;

        // Правило unique для icon с игнорированием текущей папки
        $iconUniqueRule = Rule::unique('folders')
            ->where('user_id', Auth::id())
            ->whereNull('trash_id');

        if ($this->folder) {
            $iconUniqueRule->ignore($this->folder->id);
        }

        $rules['icon'][] = $iconUniqueRule;

        $this->validate($rules, [
            'title.required' => 'Название папки обязательно',
            'title.min' => 'Название должно содержать минимум 1 символ',
            'title.max' => 'Название не должно превышать 12 символов',
            'title.unique' => 'Папка с таким названием уже существует',
            'color.required' => 'Выберите цвет папки',
            'color.regex' => 'Цвет должен быть в формате HEX (например, #FF0000)',
            'icon.required' => 'Выберите иконку папки',
            'icon.in' => 'Выберите корректную иконку из списка',
            'icon.unique' => 'Эта иконка уже используется в другой папке',
        ]);

        if ($this->folder) {
            // Обновление существующей папки
            $this->folder->title = $this->title;
            $this->folder->color = $this->color;
            $this->folder->icon = $this->icon;
            $this->folder->save();
            $message = 'Папка успешно обновлена';
            $event = 'folderUpdated';
        } else {
            // Создание новой папки
            $folder = new Folder();
            $folder->title = $this->title;
            $folder->color = $this->color;
            $folder->icon = $this->icon;
            $folder->user_id = Auth::id();
            $folder->save();
            $message = 'Папка успешно создана';
            $event = 'folderCreated';
        }

        // $this->dispatch('notify', ['message' => $message, 'type' => 'success']);
        $this->dispatch('notification', title: 'Успешно', content: 'Изменения сохранены', type: 'success');
        $this->dispatch($event);
        // $this->dispatch('navigateTo', section: 'dashboard');
    }

    public function cancel(): void
    {
        $this->back();
    }

    public function back(): void
    {
        $previousSection = StateManager::get('previous_section', 'dashboard');
        $previousFolderId = StateManager::get('previous_folderId');
        $previousNoteId = StateManager::get('previous_noteId');

        // Если предыдущая секция - сейф, возвращаемся в сейф
        if ($previousSection === 'safe') {
            $previousSection = 'safe';
            $previousFolderId = null;
            $previousNoteId = null;
        }

        $this->dispatch('navigateTo', $previousSection, $previousFolderId, $previousNoteId);
    }

    public function confirmDeletion(): void
    {
        $this->confirmingDeletion = true;
    }

    public function closeModal(): void
    {
        $this->confirmingDeletion = false;
        $this->dispatch('modalClosed');
    }

    public function openDeleteModal(): void
    {
        // Для обратной совместимости, вызываем confirmDeletion
        $this->confirmDeletion();
    }

    public function deleteFolder(): void
    {
        // Загружаем папку, если она не загружена (свойство private не сохраняется между запросами)
        $folder = $this->folder;

        if (!$folder && $this->folderId) {
            $folder = Folder::where('user_id', Auth::id())
                ->where('id', $this->folderId)
                ->active()
                ->first();
        }

        if (!$folder) {
            $this->dispatch('notify', ['message' => 'Папка не найдена', 'type' => 'error']);
            $this->confirmingDeletion = false;
            return;
        }

        $success = $folder->moveToTrash();

        if ($success) {
            $this->dispatch('notification', title: 'Удалено', content: "Папка «{$folder->title}» отправлена в корзину", type: 'danger');
            $this->dispatch('folderDeleted');
            $this->dispatch('navigateTo', section: 'dashboard');
            $this->confirmingDeletion = false;
        }
    }

    public function render()
    {
        return view('livewire.edit-folder');
    }
}