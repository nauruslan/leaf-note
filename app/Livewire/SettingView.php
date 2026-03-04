<?php

namespace App\Livewire;

use App\Services\StateManager;
use Livewire\Component;

class SettingView extends Component
{
    public function saveSettings()
    {
        // Логика сохранения настроек
        $this->dispatch('notify', ['message' => 'Настройки сохранены', 'type' => 'success']);
    }

    public function resetSettings()
    {
        // Логика сброса настроек
        $this->dispatch('notify', ['message' => 'Настройки сброшены', 'type' => 'warning']);
    }

    public function render()
    {
        return view('livewire.setting');
    }
}