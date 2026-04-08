<?php

namespace App\Livewire;

use Livewire\Component;

class SettingView extends Component
{
    public function saveSettings()
    {
        // Логика сохранения настроек
        $this->dispatch('notification', title: 'Успешно', content: 'Настройки сохранены', type: 'success');
    }

    public function resetSettings()
    {
        // Логика сброса настроек
        $this->dispatch('notification', title: 'Успешно', content: 'Настройки сброшены', type: 'warning');
    }

    public function render()
    {
        return view('livewire.setting');
    }
}