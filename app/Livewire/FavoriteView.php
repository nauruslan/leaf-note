<?php

namespace App\Livewire;

use App\Models\Note;
use Illuminate\Support\Facades\Auth;

class FavoriteView extends BaseView
{
    public string $heading = 'Избранное';
    public string $subheading = 'Ваши избранные заметки и списки';
    public bool $isFavorite = true;

    /**
     * Скоупы для избранного - только избранные активные заметки.
     */
    protected array $scopes = ['favorite', 'active'];

    /**
     * Базовые условия для избранного (пустой массив, так как условия уже применены через скоупы).
     */
    protected function getBaseConditions(): array
    {
        return [];
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