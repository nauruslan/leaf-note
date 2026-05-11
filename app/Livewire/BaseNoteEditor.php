<?php

namespace App\Livewire;

use App\Dto\LocationDto;
use App\Livewire\Traits\WithBackSection;
use App\Livewire\Traits\WithFavorite;
use App\Livewire\Traits\WithNoteStore;
use App\Services\ContentService;
use App\Services\DropdownValueParser;
use App\Services\ImageService;
use App\Services\LocationService;
use App\Services\NoteService;
use App\Services\TemporaryImageService;
use Livewire\Component;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;

abstract class BaseNoteEditor extends Component
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
    protected ImageService $imageService;
    protected TemporaryImageService $temporaryImageService;

    public function boot(
        NoteService $noteService,
        ContentService $contentService,
        LocationService $locationService,
        DropdownValueParser $dropdownParser,
        ImageService $imageService,
        TemporaryImageService $temporaryImageService,
    ): void {
        $this->noteService = $noteService;
        $this->contentService = $contentService;
        $this->locationService = $locationService;
        $this->dropdownParser = $dropdownParser;
        $this->imageService = $imageService;
        $this->temporaryImageService = $temporaryImageService;
    }

    // Общие методы
    abstract public function autoSave(bool $locationChanged = false): void;

    public function updatedDropdownValue(): void
    {
        $location = $this->dropdownParser->parse($this->dropdownValue);

        $locationChanged = ($location->folderId !== $this->originalFolderId) ||
                           ($location->safeId !== $this->originalSafeId) ||
                           ($location->archiveId !== $this->originalArchiveId);

        $this->folderId = $location->folderId;
        $this->safeId = $location->safeId;
        $this->archiveId = $location->archiveId;

        $this->autoSave($locationChanged);
    }

    #[On('updateSafeId')]
    public function setSafeId(int $id): void
    {
        $this->safeId = $id;
        $this->folderId = null;
        $this->archiveId = null;
        $this->dropdownValue = $this->locationService->buildDropdownValue(
            $this->folderId,
            $this->safeId,
            $this->archiveId
        );
        $this->autoSave();
    }

    #[On('updateArchiveId')]
    public function setArchiveId(int $id): void
    {
        $this->archiveId = $id;
        $this->folderId = null;
        $this->safeId = null;
        $this->dropdownValue = $this->locationService->buildDropdownValue(
            $this->folderId,
            $this->safeId,
            $this->archiveId
        );
        $this->autoSave();
    }

    public function updatedTitle(): void
    {
        $this->autoSave();
    }

    public function updatedContent(): void
    {
        $this->autoSave();
    }

    protected function validateAndSave(): bool
    {
        try {
            $this->validateOnly('title');
        } catch (\Illuminate\Validation\ValidationException) {
            return false;
        }

        return true;
    }

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
     * Извлечь пути изображений из контента
     */
    protected function extractImagePathsFromContent(mixed $content): array
    {
        return $this->imageService->extractImagePaths($content);
    }

    /**
     * Удалить изображения из хранилища
     */
    protected function deleteImagesFromStorage(array $paths): void
    {
        $this->imageService->deleteImagesFromStorage($paths);
    }

    /**
     * Выполнить отложенное удаление изображений
     */
    protected function executePendingImageDeletion(): void
    {
        $this->temporaryImageService->executePendingDeletion();
    }

    /**
     * Очистить временные изображения
     */
    protected function clearTemporaryImages(): void
    {
        $this->temporaryImageService->clear();
    }

    /**
     * Удалить несохранённые изображения
     */
    protected function deleteUnsavedImages(): void
    {
        $this->temporaryImageService->deleteUnsavedImages();
    }
}
