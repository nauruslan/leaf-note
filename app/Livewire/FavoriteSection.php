<?php

namespace App\Livewire;

use App\Services\NoteQueryService;
use Illuminate\Support\Facades\Auth;

class FavoriteSection extends Base
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
        return app(NoteQueryService::class)->getFavoriteNotesCount(Auth::id());
    }

    public function render()
    {
        return view('livewire.favorite');
    }
}
