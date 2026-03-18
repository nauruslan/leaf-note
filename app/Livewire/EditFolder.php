<?php
namespace App\Livewire;

use App\Livewire\NavigationSidebar;
use App\Models\Folder;
use App\Services\StateManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class EditFolder extends Component
{
    public ?int $folderId = null;
    public string $title = '';
    public string $color = 'white';
    public string $icon='folder';

    public ?Folder $folder = null;
    public bool $confirmingDeletion = false;


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

    public const ICONS = [
        'folder'      => ['label' => 'Папка',         'icon' => 'folder'],
        'folder-open' => ['label' => 'Открытая папка','icon' => 'folder-open'],
        'archive'     => ['label' => 'Архив',         'icon' => 'archive'],
        'book'        => ['label' => 'Книга',         'icon' => 'book'],
        'calendar'    => ['label' => 'Календарь',     'icon' => 'calendar'],
        'clipboard'   => ['label' => 'Буфер обмена',  'icon' => 'clipboard'],
        'cloud'       => ['label' => 'Облако',        'icon' => 'cloud'],
        'database'    => ['label' => 'База данных',   'icon' => 'database'],
        'file'        => ['label' => 'Файл',          'icon' => 'file'],
        'heart'       => ['label' => 'Сердце',        'icon' => 'heart'],
        'home'        => ['label' => 'Дом',           'icon' => 'home'],
        'star'        => ['label' => 'Звезда',        'icon' => 'star'],
    ];


    public function mount(): void
    {
        $this->folderId= StateManager::get('folderId');
        if ($this->folderId) {
            $this->folder = Folder::where('user_id', Auth::id())->find($this->folderId);
            if ($this->folder) {
                $this->title = $this->folder->title;
                $this->color = $this->folder->color;
                $this->icon = $this->folder->icon;
            }
        }
    }

    public function getColorsProperty()
    {
        return self::COLORS;
    }

    public function getIconsProperty()
    {
        return self::ICONS;
    }


    public function createFolder()
    {
        $this->save();
    }

    public function save()
    {
        try {
            $rules = [
                'title' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('folders')->where('user_id', Auth::id())->whereNull('trash_id'),
                ],
                'color' => ['required', 'string', 'in:' . implode(',', array_keys(self::COLORS))],
                'icon' => ['required', 'string', 'in:' . implode(',', array_keys(self::ICONS))],
            ];

            // Если редактируем существующую папку, игнорируем её ID в правиле unique
            if ($this->folder) {
                $rules['title'][3] = Rule::unique('folders')
                    ->where('user_id', Auth::id())
                    ->whereNull('trash_id')
                    ->ignore($this->folder->id);
            }

            $this->validate($rules);

            if ($this->folder) {
                // Обновление существующей папки
                $this->folder->title = $this->title;
                $this->folder->color = $this->color;
                $this->folder->icon = $this->icon;
                $this->folder->save();
                $message = 'Папка успешно обновлена';
            } else {
                // Создание новой папки
                $folder = new Folder();
                $folder->title = $this->title;
                $folder->color = $this->color;
                $folder->icon = $this->icon;
                $folder->user_id = Auth::id();
                $folder->save();
                $this->folder = $folder;
                $message = 'Папка успешно создана';
            }

            // Очистка кэша папок в навигационной панели
            NavigationSidebar::invalidateFoldersCache();

            $this->dispatch('notify', ['message' => $message, 'type' => 'success']);
            $this->dispatch('folderCreated');
            $this->dispatch('navigateTo', section: 'dashboard');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('notify', ['message' => 'Ошибка при сохранении папки: ' . $e->getMessage(), 'type' => 'error']);
        }
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

            // Очистка кэша папок в навигационной панели
            NavigationSidebar::invalidateFoldersCache();

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
        return view('livewire.edit-folder',['folder'=>$this->folder]);
    }
}