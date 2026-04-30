<?php

namespace App\Livewire;

use App\Livewire\Traits\WithBackSection;
use App\Models\Folder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class EditFolder extends Component
{
    use WithBackSection;

    public $heading = 'Редактирование папки';
    public $section = 'edit-folder';

    public ?int $folderId = null;
    public string $title = '';
    public string $color = '';
    public string $icon = '';
    public bool $confirmingDeletion = false;

    // Публичное свойство для отслеживания изменений
    public bool $hasUnsavedChanges = false;

    // Исходные значения для сравнения
    public string $originalTitle = '';
    public string $originalColor = '';
    public string $originalIcon = '';

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

        // Сохраняем исходные значения для отслеживания изменений
        $this->initOriginalValues();
    }

    // Инициализация оригинальных значений
    private function initOriginalValues(): void
    {
        $this->originalTitle = $this->title;
        $this->originalColor = $this->color;
        $this->originalIcon = $this->icon;
    }

    // Отслеживаем изменения полей
    public function updatedTitle(): void
    {
        $this->hasUnsavedChanges = $this->hasChanges();
    }

    public function updatedColor(): void
    {
        $this->hasUnsavedChanges = $this->hasChanges();
    }

    public function updatedIcon(): void
    {
        $this->hasUnsavedChanges = $this->hasChanges();
    }

    // Проверка наличия изменений
    public function hasChanges(): bool
    {
        if ($this->originalTitle !== $this->title) {
            return true;
        }
        if ($this->originalColor !== $this->color) {
            return true;
        }
        if ($this->originalIcon !== $this->icon) {
            return true;
        }

        return false;
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
        // Если нет изменений - не сохраняем
        if (!$this->hasChanges()) {
            $this->dispatch('notification', ['title' => 'Информация', 'content' => 'Нет изменений для сохранения', 'type' => 'info']);
            return;
        }

        // Если передан folderId, но папка не загружена, попробуем загрузить
        if ($this->folderId && !$this->folder) {
            $this->folder = Folder::where('user_id', Auth::id())
                ->where('id', $this->folderId)
                ->active()
                ->first();

            if (!$this->folder) {
                $this->dispatch('notification', ['title' => 'Ошибка', 'content' => 'Папка не найдена или у вас нет прав на её редактирование.', 'type' => 'danger']);
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
        } else {
            // Создание новой папки
            $folder = new Folder();
            $folder->title = $this->title;
            $folder->color = $this->color;
            $folder->icon = $this->icon;
            $folder->user_id = Auth::id();
            $folder->save();

        }

        // Обновляем исходные значения после сохранения
        $this->initOriginalValues();
        $this->hasUnsavedChanges = false;

        $this->dispatch('notification', ['title' => 'Успешно', 'content' => 'Изменения сохранены', 'type' => 'info']);
        // Обновляем sidebar
        $this->dispatch('refreshSidebar');
    }

    public function cancel(): void
    {
        $this->back();
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
            $this->dispatch('notification', ['title' => 'Ошибка', 'content' => 'Папка не найдена', 'type' => 'danger']);
            $this->confirmingDeletion = false;
            return;
        }

        $success = $folder->moveToTrash();

        if ($success) {
            $this->dispatch('notification', ['title' => 'Удалено', 'content' => "Папка «{$folder->title}» отправлена в корзину", 'type' => 'danger']);
            $this->dispatch('navigateTo', section: 'dashboard');
            // Обновляем sidebar (получит новое значение section через проп от AppLayout)
            $this->dispatch('refreshSidebar');
            // Закрыть модальное окно
            $this->confirmingDeletion = false;
        } else {
            // Корзина переполнена
            $this->dispatch('notification', ['title' => 'Ошибка', 'content' => 'Корзина переполнена. Очистите корзину перед удалением.', 'type' => 'danger']);
            $this->confirmingDeletion = false;
        }
    }

    public function render()
    {
        return view('livewire.edit-folder');
    }
}
