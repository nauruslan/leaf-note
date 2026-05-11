<?php

namespace App\Livewire\Traits;

use App\Services\NoteService;
use Illuminate\Support\Facades\Auth;

/**
 * Трейт для работы с избранным
 */
trait WithFavorite
{
    protected NoteService $noteService;

    /**
     * Инициализация сервиса
     */
    public function bootWithFavorite(NoteService $noteService): void
    {
        $this->noteService = $noteService;
    }

    /**
     * Обновление статуса избранного
     */
    public function updatedIsFavorite($value): void
    {
        if ($this->noteId) {
            $this->noteService->toggleFavorite(Auth::id(), $this->noteId, (bool) $value);

            $this->dispatch('notification', [
                'title' => 'Успешно',
                'content' => $value ? 'Добавлено в избранное' : 'Удалено из избранного',
                'type' => 'info',
            ]);
        } else {
            $this->autoSave();
        }

        $this->dispatch('refreshSidebar');
    }
}