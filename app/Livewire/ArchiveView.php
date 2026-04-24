<?php

namespace App\Livewire;

use App\Models\Archive;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;

class ArchiveView extends BaseView
{
    public ?int $archiveId = null;

    public string $heading = 'Архив';
    public string $subheading = 'Заметки и списки помещенные в архив';

    /**
     * Скоупы для архива - только архивированные заметки.
     */
    protected array $scopes = ['archived'];

    public function mount(): void
    {
        $archive = Archive::where('user_id', Auth::id())->first();
        if ($archive) {
            $this->archiveId = $archive->id;
        }
    }

    /**
     * Базовые условия для архива (пустой массив, так как условия уже применены через скоупы).
     */
    protected function getBaseConditions(): array
    {
        return [];
    }

    /**
     * Общее количество архивированных заметок.
     */
    protected function getTotalCount(): int
    {
        return Note::forUser(Auth::id())
            ->archived()
            ->count();
    }

    public function render()
    {
        return view('livewire.archive');
    }
}
