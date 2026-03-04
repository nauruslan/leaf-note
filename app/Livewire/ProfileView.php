<?php

namespace App\Livewire;

use App\Services\StateManager;
use Livewire\Component;

class ProfileView extends Component
{
    public function saveProfile()
    {
        // Логика сохранения профиля
        $this->dispatch('notify', ['message' => 'Профиль обновлён', 'type' => 'success']);
    }

    public function changePassword()
    {
        $this->dispatch('openModal', 'change-password');
    }

    public function render()
    {
        return view('livewire.profile');
    }
}
