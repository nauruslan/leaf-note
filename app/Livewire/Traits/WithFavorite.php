<?php

namespace App\Livewire\Traits;

use App\Models\Note;
use Illuminate\Support\Facades\Auth;


// Трейт для работы с избранным
trait WithFavorite
{
     public function updatedIsFavorite($value): void
    {
        // Если заметка уже создана, обновляем в БД
        if ($this->noteId) {
            $note = Note::where('user_id', Auth::id())
                ->find($this->noteId);

            if ($note) {
                $note->is_favorite = (bool) $value;
                $note->save();

                // Показываем уведомление
                if ($note->is_favorite) {
                    $this->dispatch('notification', title: 'Успешно', content: 'Добавлено в избранное', type: 'success');
                } else {
                    $this->dispatch('notification', title: 'Успешно', content: 'Удалено из избранного', type: 'success');
                }
                // Обновляем sidebar
                $this->dispatch('refreshSidebar');
            }
        } else {
            // Заметка еще не создана, просто вызываем автосохранение
            $this->autoSave();
        }
    }
}