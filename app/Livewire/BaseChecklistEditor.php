<?php

namespace App\Livewire;

use App\Livewire\Traits\WithBackSection;
use App\Livewire\Traits\WithFavorite;
use App\Livewire\Traits\WithNoteStore;
use App\Services\ContentService;
use App\Services\DropdownValueParser;
use App\Services\LocationService;
use App\Services\NoteService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Livewire\Component;

/**
 * Базовый класс для редакторов чеклистов
 */
abstract class BaseChecklistEditor extends Component
{
    use WithBackSection;
    use WithNoteStore;
    use WithFavorite;

    // Публичные свойства для UI
    #[Rule('required|string|max:255')]
    public string $title = '';

    public ?int $folderId = null;
    public ?int $safeId = null;
    public ?int $archiveId = null;
    public ?string $dropdownValue = null;
    public bool $is_favorite = false;
    public string $content = '';
    public bool $isSaving = false;

    // Защищённые от параллельных запросов
    #[Locked]
    public ?int $noteId = null;

    // Оригинальное местоположение для отслеживания изменений
    protected ?int $originalFolderId = null;
    protected ?int $originalSafeId = null;
    protected ?int $originalArchiveId = null;

    // Внедряемые сервисы
    protected NoteService $noteService;
    protected ContentService $contentService;
    protected LocationService $locationService;
    protected DropdownValueParser $dropdownParser;

    public function boot(
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

    /**
     * Обновление dropdown значения
     */
    public function updatedDropdownValue(): void
    {
        $location = $this->dropdownParser->parse($this->dropdownValue);

        $locationChanged = $this->locationService->locationChanged(
            $location,
            $this->originalFolderId,
            $this->originalSafeId,
            $this->originalArchiveId,
        );

        $this->folderId = $location->folderId;
        $this->safeId = $location->safeId;
        $this->archiveId = $location->archiveId;

        $this->autoSave($locationChanged);
    }

    /**
     * Установить ID сейфа
     */
    #[On('updateSafeId')]
    public function setSafeId(int $id): void
    {
        $this->safeId = $id;
        $this->folderId = null;
        $this->archiveId = null;
        $this->dropdownValue = $this->locationService->buildDropdownValue(
            $this->folderId,
            $this->safeId,
            $this->archiveId,
        );
        $this->autoSave();
    }

    /**
     * Установить ID архива
     */
    #[On('updateArchiveId')]
    public function setArchiveId(int $id): void
    {
        $this->archiveId = $id;
        $this->folderId = null;
        $this->safeId = null;
        $this->dropdownValue = $this->locationService->buildDropdownValue(
            $this->folderId,
            $this->safeId,
            $this->archiveId,
        );
        $this->autoSave();
    }

    /**
     * Обновление safeId
     */
    public function updatedSafeId(): void
    {
        $this->autoSave();
    }

    /**
     * Обновление title
     */
    public function updatedTitle(): void
    {
        $this->autoSave();
    }

    /**
     * Обновление content
     */
    public function updatedContent(): void
    {
        $this->autoSave();
    }

    /**
     * Валидация и сохранение
     */
    protected function validateAndSave(): bool
    {
        try {
            $this->validateOnly('title');
        } catch (\Illuminate\Validation\ValidationException) {
            return false;
        }

        return true;
    }

    /**
     * Отправить уведомление об изменении местоположения
     */
    protected function dispatchLocationChangedNotification(\App\Models\Note $note): void
    {
        $locationName = $this->locationService->getLocationName($note);
        $this->dispatch('notification', [
            'title' => 'Обновлено',
            'content' => "Место хранения изменено на «{$locationName}»",
            'type' => 'info',
        ]);
    }

    /**
     * Абстрактный метод автосохранения
     */
    abstract public function autoSave(bool $locationChanged = false): void;
}
