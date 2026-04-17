<?php
namespace App\Livewire;

use App\Models\Folder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class EditFolder extends Component
{
    public $heading='Редактирование папки';

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

    public function getColorsProperty(): array
    {
        return Folder::COLORS;
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

    /**
     * Получить занятые цвета текущего пользователя (исключая текущую папку)
     */
    public function getUsedColorsProperty(): array
    {
        $query = Folder::where('user_id', Auth::id())
            ->whereNull('trash_id');

        if ($this->folder) {
            $query->where('id', '!=', $this->folder->id);
        }

        return $query->pluck('color')->toArray();
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
                'in:' . implode(',', array_keys(Folder::COLORS)),
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

        // Правило unique для color с игнорированием текущей папки
        $colorUniqueRule = Rule::unique('folders')
            ->where('user_id', Auth::id())
            ->whereNull('trash_id');

        if ($this->folder) {
            $colorUniqueRule->ignore($this->folder->id);
        }

        $rules['color'][] = $colorUniqueRule;

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
            'color.in' => 'Выберите корректный цвет из списка',
            'color.unique' => 'Этот цвет уже используется в другой папке',
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

        $this->dispatch('notify', ['message' => $message, 'type' => 'success']);
        $this->dispatch($event);
        $this->dispatch('navigateTo', section: 'dashboard');
    }

    public function cancel()
    {
        $this->dispatch('navigateTo', section: 'dashboard');
    }

    public function confirmDeletion()
    {
        $this->confirmingDeletion = true;
    }

    public function closeModal()
    {
        $this->confirmingDeletion = false;
        $this->dispatch('modalClosed');
    }

    public function openDeleteModal()
    {
        // Для обратной совместимости, вызываем confirmDeletion
        $this->confirmDeletion();
    }

    public function deleteFolder()
    {
        if (!$this->folder) {
            $this->dispatch('notify', ['message' => 'Папка не найдена', 'type' => 'error']);
            return;
        }

        try {
            // Удаляем папку (мягкое удаление через trash)
            $this->folder->moveToTrash();

            $this->dispatch('notify', ['message' => 'Папка удалена', 'type' => 'success']);
            $this->dispatch('folderDeleted');
            $this->dispatch('navigateTo', section: 'dashboard');
            $this->confirmingDeletion = false;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('notify', ['message' => 'Ошибка при удалении папки: ' . $e->getMessage(), 'type' => 'error']);
        }
    }

    public function render()
    {
        return view('livewire.edit-folder');
    }
}
