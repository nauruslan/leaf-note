<?php
namespace App\Livewire;

use App\Services\StateManager;
use Livewire\Component;
use Illuminate\Support\Facades\Session;

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
        $this->section = StateManager::get('section', 'dashboard');
        $this->folderId = StateManager::get('folderId');
        $this->search = StateManager::get('search', '');
    }

    public function handleNavigateTo(string $section, ?int $folderId = null): void
    {
        $this->section = $section;
        $this->folderId = $folderId;

        StateManager::set('section', $this->section);
        StateManager::set('folderId', $this->folderId);

        Session::put('sidebar_expanded', true);

        $this->dispatch('stateUpdated',
            section: $this->section,
            folderId: $this->folderId,
            search: $this->search
        );

    }

    public function handleSearchUpdated(string $search): void
    {
        $this->search = $search;
        StateManager::set('search', $this->search);

        $this->dispatch('stateUpdated',
            section: $this->section,
            folderId: $this->folderId,
            search: $this->search
        );
    }

    public function updateState(string $key, mixed $value): void
    {
        StateManager::set($key, $value);
        $this->dispatch('stateChanged', key: $key, value: $value);
    }

    public function getState(string $key): mixed
    {
        return StateManager::get($key);
    }

    public function setState(array $data): void
    {
        StateManager::setMultiple($data);
        $this->dispatch('batchStateChanged', data: $data);
    }

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
