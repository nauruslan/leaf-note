<?php
namespace App\Livewire;

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
            $rules = [
                'title' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('folders')->where('user_id', Auth::id())->whereNull('trash_id'),
                ],
                'color' => ['required', 'string', 'in:' . implode(',', array_keys(Folder::COLORS))],
                'icon' => ['required', 'string', 'in:' . implode(',', array_keys(Folder::ICONS))],
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
