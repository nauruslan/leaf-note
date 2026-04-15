<?php

namespace App\Livewire;

use App\Livewire\Traits\WithFavorite;
use App\Livewire\Traits\WithFolderSafeSelection;
use App\Models\Note;
use App\Services\StateManager;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class CreateChecklistView extends Component
{
    use WithFolderSafeSelection;
    use WithFavorite;

    private const EMPTY_CHECKLIST_STRUCTURE = '{"type":"doc","content":[{"type":"checklist","content":[]}]}';

    public $heading = 'Создать список';
    public $section = 'create-checklist';

    public string $title = '';
    public ?int $folderId = null;
    public ?int $safeId = null;
    public ?int $archiveId = null;
    public ?string $dropdownValue = null;
    public bool $is_favorite = false;
    public string $content = '';
    public bool $confirmingDeletion = false;
    public bool $isSaving = false;
    public ?int $noteId = null;

    private ?Note $cachedNote = null;

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
        ];
    }

    public function mount(): void
    {
        $this->content = self::EMPTY_CHECKLIST_STRUCTURE;

        $presetSafeId = StateManager::get('preset_safe_id');
        if ($presetSafeId) {
            $this->safeId = $presetSafeId;
            StateManager::remove('preset_safe_id');
            return;
        }

        $presetArchiveId = StateManager::get('preset_archive_id');
        if ($presetArchiveId) {
            $this->archiveId = $presetArchiveId;
            StateManager::remove('preset_archive_id');
            return;
        }

        $presetFolderId = StateManager::get('preset_folder_id');
        if ($presetFolderId) {
            $this->folderId = $presetFolderId;
            StateManager::remove('preset_folder_id');
        }
    }

    public function cancel(): void
    {
        $this->dispatch('navigateTo', 'dashboard');
    }

    public function saveWithLocation(): void
    {
        if ($this->isSafeSelected($this->folderId)) {
            $this->safeId = $this->folderId;
            $this->folderId = null;
        }

        if ($this->isArchiveSelected($this->folderId)) {
            $this->archiveId = $this->folderId;
            $this->folderId = null;
        }

        $this->save();
    }

    public function save(): void
    {
        try {
            $this->validateOnly('title');
        } catch (\Illuminate\Validation\ValidationException) {
            $this->dispatch('showError', 'Название обязательно и не должно превышать 255 символов');
            return;
        }

        try {
            // Если заметка уже создана через автосохранение, обновляем ее
            if ($this->noteId) {
                if (!$this->cachedNote) {
                    $this->cachedNote = Note::where('user_id', Auth::id())
                        ->where('type', Note::TYPE_CHECKLIST)
                        ->find($this->noteId);
                }

                if (!$this->cachedNote) {
                    $this->dispatch('notification', title: 'Ошибка', content: 'Список не найден', type: 'danger');
                    return;
                }

                $this->updateTitle($this->cachedNote);
                $this->updateContent($this->cachedNote);
                $this->updateLocation($this->cachedNote);
                $this->cachedNote->save();
            } else {
                // Создаем новую заметку
                $note = new Note();
                $note->title = trim($this->title);
                $note->type = Note::TYPE_CHECKLIST;
                $note->content = $this->normalizeContent($this->content);
                $note->is_favorite = $this->is_favorite;
                $note->user_id = Auth::id();

                if ($this->folderId !== null) {
                    $note->folder_id = $this->folderId;
                    $note->safe_id = null;
                    $note->archive_id = null;
                } elseif ($this->safeId !== null) {
                    $note->safe_id = $this->safeId;
                    $note->folder_id = null;
                    $note->archive_id = null;
                } elseif ($this->archiveId !== null) {
                    $note->archive_id = $this->archiveId;
                    $note->folder_id = null;
                    $note->safe_id = null;
                } else {
                    $note->archive_id = Auth::user()->archive->id;
                }

                $note->save();
                $this->noteId = $note->id;
                $this->cachedNote = $note;
            }

            $this->dispatch('checklistCreated');
            $this->dispatch('navigateTo', 'dashboard');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('notification', title: 'Ошибка', content: 'Не удалось сохранить список', type: 'danger');
        }
    }

    public function updatedDropdownValue(): void
    {
        // Обработка префиксов safe_ и archive_
        if (is_string($this->dropdownValue)) {
            if (str_starts_with($this->dropdownValue, 'safe_')) {
                $this->safeId = (int) substr($this->dropdownValue, 5);
                $this->folderId = null;
                $this->archiveId = null;
            } elseif (str_starts_with($this->dropdownValue, 'archive_')) {
                $this->archiveId = (int) substr($this->dropdownValue, 8);
                $this->folderId = null;
                $this->safeId = null;
            } elseif (is_numeric($this->dropdownValue)) {
                $this->folderId = (int) $this->dropdownValue;
                $this->safeId = null;
                $this->archiveId = null;
            }
        }
        $this->autoSave();
    }

    public function updatedFolderId(): void
    {
        // Этот метод может вызываться если folderId изменяется напрямую
        $this->autoSave();
    }

    #[On('updateSafeId')]
    public function setSafeId(int $id): void
    {
        $this->safeId = $id;
        $this->folderId = null;
        $this->archiveId = null;
        $this->autoSave();
    }

    #[On('updateArchiveId')]
    public function setArchiveId(int $id): void
    {
        $this->archiveId = $id;
        $this->folderId = null;
        $this->safeId = null;
        $this->autoSave();
    }

    public function updatedSafeId(): void
    {
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

    #[On('checklistContentReady')]
    public function handleContentReady(string $content): void
    {
        $this->content = $content;
        $this->save();
    }

    public function autoSave(): void
    {
        // Если выбранный folderId является сейфом, перемещаем его в safeId
        if ($this->folderId && $this->isSafeSelected($this->folderId)) {
            $this->safeId = $this->folderId;
            $this->folderId = null;
        }

        // Если выбранный folderId является архивом, перемещаем его в archiveId
        if ($this->folderId && $this->isArchiveSelected($this->folderId)) {
            $this->archiveId = $this->folderId;
            $this->folderId = null;
        }

        // Условия автосохранения:
        // 1. Должна быть выбрана папка (folderId) ИЛИ сейф (safeId) ИЛИ архив (archiveId)
        // 2. Title должен иметь длину хотя бы 1 символ
        if (($this->folderId === null && $this->safeId === null && $this->archiveId === null) || trim($this->title) === '') {
            return;
        }

        try {
            $this->validateOnly('title');
        } catch (\Illuminate\Validation\ValidationException) {
            // При автосохранении не показываем ошибку, просто пропускаем
            return;
        }

        $this->isSaving = true;

        try {
            // Если заметка уже создана, обновляем ее
            if ($this->noteId) {
                if (!$this->cachedNote) {
                    $this->cachedNote = Note::where('user_id', Auth::id())
                        ->where('type', Note::TYPE_CHECKLIST)
                        ->find($this->noteId);
                }

                if (!$this->cachedNote) {
                    $this->isSaving = false;
                    return;
                }

                $this->updateTitle($this->cachedNote);
                $this->updateContent($this->cachedNote);
                $this->updateLocation($this->cachedNote);
                $this->cachedNote->save();
            } else {
                // Создаем новую заметку
                $note = new Note();
                $note->title = trim($this->title);
                $note->type = Note::TYPE_CHECKLIST;
                $note->content = $this->normalizeContent($this->content);
                $note->is_favorite = $this->is_favorite;
                $note->user_id = Auth::id();

                if ($this->folderId !== null) {
                    $note->folder_id = $this->folderId;
                    $note->safe_id = null;
                    $note->archive_id = null;
                } elseif ($this->safeId !== null) {
                    $note->safe_id = $this->safeId;
                    $note->folder_id = null;
                    $note->archive_id = null;
                } elseif ($this->archiveId !== null) {
                    $note->archive_id = $this->archiveId;
                    $note->folder_id = null;
                    $note->safe_id = null;
                } else {
                    // Не должно происходить, т.к. проверка выше
                    $this->isSaving = false;
                    return;
                }

                $note->save();

                // Сохраняем ID созданной заметки для будущих обновлений
                $this->noteId = $note->id;
                $this->cachedNote = $note;
            }
        } catch (\Throwable $e) {
            report($e);
            // При автосохранении не показываем ошибку пользователю
        } finally {
            $this->isSaving = false;
        }
    }

    private function normalizeContent(mixed $content): string
    {
        if (is_string($content) && $content === '') {
            return self::EMPTY_CHECKLIST_STRUCTURE;
        }

        if (! is_string($content)) {
            if (is_array($content) || is_object($content)) {
                $content = json_encode($content);
            } else {
                return self::EMPTY_CHECKLIST_STRUCTURE;
            }
        }

        try {
            $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

            if (! is_array($decoded) || empty($decoded)) {
                return self::EMPTY_CHECKLIST_STRUCTURE;
            }

            return json_encode($decoded, JSON_UNESCAPED_UNICODE);
        } catch (\JsonException) {
            return self::EMPTY_CHECKLIST_STRUCTURE;
        }
    }

    private function updateTitle(Note $note): void
    {
        $note->title = trim($this->title);
    }

    private function updateContent(Note $note): void
    {
        $note->content = $this->content;
    }

    private function updateLocation(Note $note): void
    {
        if ($this->folderId !== null) {
            $note->folder_id = $this->folderId;
            $note->safe_id = null;
            $note->archive_id = null;
        } elseif ($this->safeId !== null) {
            $note->safe_id = $this->safeId;
            $note->folder_id = null;
            $note->archive_id = null;
        } elseif ($this->archiveId !== null) {
            $note->archive_id = $this->archiveId;
            $note->folder_id = null;
            $note->safe_id = null;
        } else {
            $note->folder_id = null;
            $note->safe_id = null;
            $note->archive_id = Auth::user()->archive->id;
        }
    }

    private function isSafeSelected(?int $selectedId): bool
    {
        if ($selectedId === null) {
            return false;
        }

        // Проверяем с префиксом safe_
        return collect($this->safes)->contains('value', 'safe_' . $selectedId);
    }

    private function isArchiveSelected(?int $selectedId): bool
    {
        if ($selectedId === null) {
            return false;
        }

        // Проверяем с префиксом archive_
        return collect($this->archives)->contains('value', 'archive_' . $selectedId);
    }

    public function back(): void
    {
        $previousSection = StateManager::get('previous_section', 'dashboard');
        $previousFolderId = StateManager::get('previous_folderId');
        $previousNoteId = StateManager::get('previous_noteId');

        // Если предыдущая секция - сейф, возвращаемся в сейф
        if ($previousSection === 'safe') {
            $previousSection = 'safe';
            $previousFolderId = null;
            $previousNoteId = null;
        }

        $this->dispatch('navigateTo', $previousSection, $previousFolderId, $previousNoteId);
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.create-checklist');
    }
}