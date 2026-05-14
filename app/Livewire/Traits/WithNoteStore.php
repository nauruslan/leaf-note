<?php

namespace App\Livewire\Traits;

use App\Services\FolderService;
use App\Services\LocationService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

/**
 * Трейт для подгрузки списка папок, сейфов и архивов
 */
trait WithNoteStore
{
    // Сервисы будут инициализированы в BaseEditor через boot()

    /**
     * Получить список папок
     */
    #[Computed]
    public function folders(): EloquentCollection
    {
        return $this->folderService->getActiveFolders(Auth::id());
    }

    /**
     * Получить список сейфов для dropdown
     */
    #[Computed]
    public function safes(): Collection
    {
        return $this->locationService->getSafesForDropdown(Auth::id());
    }

    /**
     * Получить список архивов для dropdown
     */
    #[Computed]
    public function archives(): Collection
    {
        return $this->locationService->getArchivesForDropdown(Auth::id());
    }
}