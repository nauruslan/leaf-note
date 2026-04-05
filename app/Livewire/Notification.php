<?php

namespace App\Livewire;

use Livewire\Component;

class Notification extends Component
{
    protected $listeners = [
        'show-notification' => 'handleNotification'
    ];

    public function handleNotification(string $title, string $content, string $type = 'info'): void
    {
        $this->dispatch('notification-js', title: $title, content: $content, type: $type);
    }

    public function render()
    {
        return view('livewire.notification');
    }
}