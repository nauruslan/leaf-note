<?php
namespace App\Livewire;
use Livewire\Component;

class CreateFolderView extends Component
{
    public function createFolder()
    {
        // Логика создания папки
        $this->dispatch('notify', ['message' => 'Папка создана', 'type' => 'success']);
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