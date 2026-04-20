<?php

namespace App\Livewire;

use App\Models\Note;
use Illuminate\Support\Facades\Auth;

class FavoriteView extends BaseView
{
    public string $heading = 'Избранное';
    public string $subheading = 'Ваши избранные заметки и списки';

    /**
     * Базовые условия для избранного - только избранные активные заметки.
     */
    protected function getBaseConditions(): array
    {
        return [
            'is_favorite' => true,
            'trash_id' => null,
            'archive_id' => null,
            'safe_id' => null,
        ];
    }

    /**
     * Общее количество избранных заметок.
     */
    protected function getTotalCount(): int
    {
        return Note::forUser(Auth::id())
            ->favorite()
            ->active()
            ->count();
    }

    public function render()
    {
        return view('livewire.favorite');
    }
}