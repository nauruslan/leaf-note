<?php

namespace App\Livewire;

use App\Models\Archive;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;

class ArchiveView extends BaseView
{
    public string $heading = 'Архив';
    public string $subheading = 'Заметки и списки помещенные в архив';

    public ?Archive $archive = null;

    public function mount(): void
    {
        $this->archive = Archive::where('user_id', Auth::id())->first();
    }

    /**
     * Базовые условия для архива - заметки с archive_id.
     */
    protected function getBaseConditions(): array
    {
        return [
            'whereNotNull:archive_id' => true,
        ];
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