<?php

namespace App\Livewire;

use App\Services\StateManager;
use Livewire\Component;

class AppState extends Component
{
    public string $section = 'dashboard';
    public ?int $folderId = null;
    public string $search = '';

    protected $listeners = [
        'navigateTo'    => 'handleNavigateTo',
        'searchUpdated' => 'handleSearchUpdated',
        'updateState'   => 'updateState',
        'getState'      => 'getState',
    ];

    public function mount(): void
    {
        // Загрузка начального состояния из сервиса
        $this->section = StateManager::get('section', 'dashboard');
        $this->folderId = StateManager::get('folderId');
        $this->search = StateManager::get('search', '');
    }

    /**
     * Обработчик навигации между разделами
     */
    public function handleNavigateTo(string $section, ?int $folderId = null): void
    {
        $this->section = $section;
        $this->folderId = $folderId;

        // Сохраняем состояние в сессии через сервис
        StateManager::set('section', $this->section);
        StateManager::set('folderId', $this->folderId);

        $this->dispatch('stateUpdated',
            section: $this->section,
            folderId: $this->folderId,
            search: $this->search
        );
    }

    /**
     * Обработчик обновления поиска
     */
    public function handleSearchUpdated(string $search): void
    {
        $this->search = $search;

        // Сохраняем состояние в сервисе
        StateManager::set('search', $this->search);

        $this->dispatch('stateUpdated',
            section: $this->section,
            folderId: $this->folderId,
            search: $this->search
        );
    }

    /**
     * Обновление произвольных данных состояния
     */
    public function updateState(string $key, mixed $value): void
    {
        StateManager::set($key, $value);

        $this->dispatch('stateChanged',
            key: $key,
            value: $value
        );
    }

    /**
     * Получение значения из состояния
     */
    public function getState(string $key): mixed
    {
        return StateManager::get($key);
    }

    /**
     * Пакетное обновление состояния
     */
    public function setState(array $data): void
    {
        StateManager::setMultiple($data);

        $this->dispatch('batchStateChanged', data: $data);
    }

    /**
     * Сброс состояния
     */
    public function resetState(?string $key = null): void
    {
        if ($key !== null) {
            StateManager::remove($key);
        } else {
            StateManager::clear();
        }

        $this->dispatch('stateReset', key: $key);
    }

    public function render()
    {
        return view('livewire.app-state');
    }
}