<?php

namespace App\Livewire;

use App\Services\ContentService;
use App\Services\DropdownValueParser;
use App\Services\LocationService;
use App\Services\NoteService;

/**
 * Базовый класс для редакторов чеклистов
 */
abstract class BaseChecklistEditor extends BaseEditor
{
    /**
     * Инициализация сервисов для чеклистов
     */
    public function bootBaseChecklistEditor(
        NoteService $noteService,
        ContentService $contentService,
        LocationService $locationService,
        DropdownValueParser $dropdownParser,
    ): void {
        $this->noteService = $noteService;
        $this->contentService = $contentService;
        $this->locationService = $locationService;
        $this->dropdownParser = $dropdownParser;
    }
}