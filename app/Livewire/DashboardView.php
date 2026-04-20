<?php

namespace App\Livewire;

use App\Models\Note;
use Illuminate\Support\Facades\Auth;

class DashboardView extends BaseView
{
    public string $heading = 'Главная доска';
    public string $subheading = 'Все ваши заметки и списки в одном месте';

    /**
     * Базовые условия для dashboard - только активные заметки.
     */
    protected function getBaseConditions(): array
    {
        return [
            'trash_id' => null,
            'archive_id' => null,
            'safe_id' => null,
        ];
    }

    /**
     * Общее количество активных заметок.
     */
    protected function getTotalCount(): int
    {
        return Note::forUser(Auth::id())
            ->active()
            ->count();
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
