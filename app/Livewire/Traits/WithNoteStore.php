<?php

namespace App\Livewire\Traits;

use App\Models\Folder;
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
    protected LocationService $locationService;

    /**
     * Инициализация сервиса
     */
    public function bootWithNoteStore(LocationService $locationService): void
    {
        $this->locationService = $locationService;
    }

    /**
     * Получить список папок
     */
    #[Computed]
    public function folders(): EloquentCollection
    {
        return Folder::forUser(Auth::user())
            ->active()
            ->orderBy('title')
            ->get();
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