<?php

namespace App\Livewire;

use App\Services\ArchiveService;
use Illuminate\Support\Facades\Auth;

class ArchiveSection extends Base
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
        $this->archiveId = app(ArchiveService::class)->getUserArchiveId(Auth::id());
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
        return app(ArchiveService::class)->getArchivedNotesCount(Auth::id());
    }

    public function render()
    {
        return view('livewire.archive');
    }
}