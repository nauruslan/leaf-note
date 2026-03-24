<?php

namespace App\Livewire\Traits;

use App\Models\Note;
use Illuminate\Support\Facades\Auth;

/**
 * Трейт для работы с избранным.
 * Добавляет метод toggleFavorite().
 */
trait WithFavorite
{
    public function toggleFavorite(int $noteId): void
    {
        $note = Note::where('user_id', Auth::id())->find($noteId);

        if ($note) {
            $note->toggleFavorite();
            $this->dispatch('refreshSidebar');
        }
    }
}
