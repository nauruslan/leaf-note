<?php

namespace App\Livewire;

use App\Models\Note;
use Illuminate\Support\Facades\Auth;

class DashboardView extends BaseView
{
    public string $heading = 'Главная доска';
    public string $subheading = 'Все ваши заметки и списки в одном месте';

    /**
     * Скоупы для dashboard - только активные заметки.
     */
    protected array $scopes = ['active'];

    /**
     * Базовые условия для dashboard (пустой массив, так как условия уже применены через скоупы).
     */
    protected function getBaseConditions(): array
    {
        return [];
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