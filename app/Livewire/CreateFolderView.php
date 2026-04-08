<?php
namespace App\Livewire;

use App\Models\Folder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class CreateFolderView extends Component
{
    public $heading='Создать папку';
    public $subheading='Создайте новую папку для организации заметок';

    public string $title = '';
    public string $color = 'white';
    public string $icon='folder';


    public function getColorsProperty(): array
    {
        return Folder::COLORS;
    }

    public function getIconsProperty(): array
    {
        return Folder::ICONS;
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
                'color' => ['required', 'string', 'in:' . implode(',', array_keys(Folder::COLORS))],
                'icon' => ['required', 'string', 'in:' . implode(',', array_keys(Folder::ICONS))],
            ]);

            $folder = new Folder();
            $folder->title = $this->title;
            $folder->color = $this->color;
            $folder->icon = $this->icon;
            $folder->user_id = Auth::id();
            $folder->save();

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

    public function render()
    {
        return view('livewire.create-folder');
    }
}
