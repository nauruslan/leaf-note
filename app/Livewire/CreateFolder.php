<?php
namespace App\Livewire;

use App\Livewire\Traits\WithBackSection;
use App\Models\Folder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class CreateFolder extends Component
{
    use WithBackSection;

    public $heading='Создать папку';
    public $section='create-folder';

    public string $title = '';
    public string $color = '';
    public string $icon = '';

    public function getIconsProperty(): array
    {
        return Folder::ICONS;
    }

    /**
     * Получить занятые иконки текущего пользователя
     */
    public function getUsedIconsProperty(): array
    {
        return Folder::where('user_id', Auth::id())
            ->whereNull('trash_id')
            ->pluck('icon')
            ->toArray();
    }

    public function createFolder()
    {
        $this->save();
    }

    public function save()
    {
        $this->validate([
            'title' => [
                'required',
                'string',
                'min:1',
                'max:12',
                Rule::unique('folders')->where('user_id', Auth::id())->whereNull('trash_id'),
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
                Rule::unique('folders')->where('user_id', Auth::id())->whereNull('trash_id'),
            ],
        ], [
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

        $folder = new Folder();
        $folder->title = $this->title;
        $folder->color = $this->color;
        $folder->icon = $this->icon;
        $folder->user_id = Auth::id();
        $folder->save();

        $this->reset(['title', 'color', 'icon']);
        $this->title = '';
        $this->color = '';
        $this->icon = '';

        // Отправляем уведомление
        $this->dispatch('notification', ['title' => 'Успешно', 'content' => "Папка «{$folder->title}» успешно создана", 'type' => 'success']);
        // Обновляем sidebar
        $this->dispatch('refreshSidebar');
        // Переходим к папке
        $this->dispatch('navigateTo', section:'folder', folderId:$folder->id);
    }

    public function render()
    {
        return view('livewire.create-folder');
    }
}
