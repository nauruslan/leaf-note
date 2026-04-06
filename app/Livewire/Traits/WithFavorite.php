<?php

namespace App\Livewire\Traits;

use App\Models\Note;
use Illuminate\Support\Facades\Auth;


// Трейт для работы с избранным.
trait WithFavorite
{
    public function toggleFavorite(int $noteId): void
    {
        $note = Note::where('user_id', Auth::id())->find($noteId);

        if ($note) {
            $note->toggleFavorite();
            if($note->is_favorite){
                $this->dispatch('notification', title: 'Успешно', content: 'Добавлено в избранное', type: 'success');
            }else{
                $this->dispatch('notification', title: 'Успешно', content: 'Удалено из избранного', type: 'success');
            }
            $this->dispatch('refreshSidebar');
        }
    }
}