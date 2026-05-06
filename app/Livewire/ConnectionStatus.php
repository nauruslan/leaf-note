<?php

namespace App\Livewire;

use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class ConnectionStatus extends Component
{
    /**
     * Статус соединения: true - онлайн, false - оффлайн.
     */
    #[Locked]
    public bool $online = true;

    /**
     * Слушатели событий.
     */
    protected $listeners = [
        'app-offline' => 'goOffline',
        'app-online' => 'goOnline',
    ];

    /**
     * Перевод компонента в оффлайн-режим.
     * Renderless - не отправляет запрос к серверу.
     */
    #[Renderless]
    public function goOffline(): void
    {
        logger('ConnectionStatus: goOffline called');
        $this->online = false;
    }

    /**
     * Перевод компонента в онлайн-режим.
     * Renderless - не отправляет запрос к серверу.
     */
    #[Renderless]
    public function goOnline(): void
    {
        logger('ConnectionStatus: goOnline called');
        $this->online = true;
    }

    /**
     * Рендеринг компонента.
     */
    public function render(): \Illuminate\View\View
    {
        return view('livewire.connection-status');
    }
}