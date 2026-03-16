<?php
namespace App\Livewire;

use App\Models\Folder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use App\Livewire\NavigationSidebar;

class CreateFolderView extends Component
{

    public string $title = '';
    public string $color = 'white';
    public string $icon='folder';


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
            $this->validate([
                'title' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('folders')->where('user_id', Auth::id())->whereNull('trash_id'),
                ],
                'color' => ['required', 'string', 'in:' . implode(',', array_keys(self::COLORS))],
                'icon' => ['required', 'string', 'in:' . implode(',', array_keys(self::ICONS))],
            ]);

            $folder = new Folder();
            $folder->title = $this->title;
            $folder->color = $this->color;
            $folder->icon = $this->icon;
            $folder->user_id = Auth::id();
            $folder->save();

            // Очистка кэша папок в навигационной панели
            NavigationSidebar::invalidateFoldersCache();

            $this->reset(['title', 'color', 'icon']);
            $this->title = '';
            $this->color = 'white';
            $this->icon = 'folder';

            $this->dispatch('notify', ['message' => 'Папка успешно создана', 'type' => 'success']);
            $this->dispatch('folderCreated');
            $this->dispatch('navigateTo', section: 'dashboard');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('notify', ['message' => 'Ошибка при создании папки: ' . $e->getMessage(), 'type' => 'error']);
        }
    }

    public function cancel()
    {
        $this->dispatch('navigate', ['section' => 'dashboard']);
    }

    public function render()
    {
        return view('livewire.create-folder');
    }
}