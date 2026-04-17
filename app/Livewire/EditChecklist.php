<?php

namespace App\Livewire;

use App\Livewire\Traits\WithFavorite;
use App\Livewire\Traits\WithFolderSafeSelection;
use App\Models\Archive;
use App\Models\Folder;
use App\Models\Note;
use App\Models\Safe;
use App\Services\StateManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class EditChecklist extends Component
{
    use WithFolderSafeSelection;
    use WithFavorite;

    public $section='edit-checklist';

    private const EMPTY_CHECKLIST_STRUCTURE = '{"type":"doc","content":[{"type":"checklist","content":[]}]}';

    public ?int $noteId = null;
    public string $title = '';
    public ?int $folderId = null;
    public ?int $safeId = null;
    public ?int $archiveId = null;
    public ?int $dropdownFolderId = null;
    public ?string $dropdownValue = null;
    public string $content = '';
    public bool $confirmingDeletion = false;
    public bool $isSaving = false;
    public bool $is_favorite = false;

    // Для отслеживания изменений местоположения
    public ?int $originalFolderId = null;
    public ?int $originalSafeId = null;
    public ?int $originalArchiveId = null;

    private ?Note $cachedChecklist = null;

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
        ];
    }

    public function mount(?int $noteId = null, ?int $folderId = null): RedirectResponse|null
    {
        $this->noteId = $noteId;

        if ($noteId === null) {
            return redirect()->route('dashboard')->with('notification', [
                'title' => 'Внимание',
                'content' => 'Список не найден. Вы будете перенаправлены на главную страницу.',
                'type' => 'warning',
            ]);
        }

        $this->cachedChecklist = Note::where('user_id', Auth::id())
            ->where('type', Note::TYPE_CHECKLIST)
            ->find($noteId);

        if (!$this->cachedChecklist) {
            return redirect()->route('dashboard')->with('notification', [
                'title' => 'Внимание',
                'content' => 'Список не найден. Вы будете перенаправлены на главную страницу.',
                'type' => 'warning',
            ]);
        }

        $this->title = $this->cachedChecklist->title;
        $this->folderId = $this->cachedChecklist->folder_id;
        $this->safeId = $this->cachedChecklist->safe_id;
        $this->archiveId = $this->cachedChecklist->archive_id;
        $this->is_favorite = (bool) $this->cachedChecklist->is_favorite;
        $this->content = $this->normalizeContent($this->cachedChecklist->content);

        // Сохраняем оригинальное местоположение для отслеживания изменений
        $this->originalFolderId = $this->cachedChecklist->folder_id;
        $this->originalSafeId = $this->cachedChecklist->safe_id;
        $this->originalArchiveId = $this->cachedChecklist->archive_id;

        // Инициализируем dropdownValue в зависимости от того, где находится список
        if ($this->safeId) {
            $this->dropdownValue = 'safe_' . $this->safeId;
        } elseif ($this->archiveId) {
            $this->dropdownValue = 'archive_' . $this->archiveId;
        } elseif ($this->folderId) {
            $this->dropdownValue = (string) $this->folderId;
        }

        $this->dispatch('checklistLoaded', content: $this->content);

        return null;
    }

    #[Computed]
    public function checklist(): ?Note
    {
        return $this->noteId
            ? Note::where('user_id', Auth::id())
                ->where('type', Note::TYPE_CHECKLIST)
                ->find($this->noteId)
            : null;
    }

    public function cancel(): void
    {
        $this->dispatch('navigateTo', 'dashboard', null, false);
    }

    public function confirmDelete(): void
    {
        $checklist = $this->checklist();

        if (!$checklist) {
            $this->dispatch('notification', title: 'Ошибка', content: 'Список не найден', type: 'danger');
            return;
        }

        if ($checklist->is_favorite) {
            $checklist->toggleFavorite();
        }

        if (!$checklist->moveToTrash()) {
            $this->dispatch('notification', title: 'Ошибка', content: 'Не удалось удалить список', type: 'danger');
            return;
        }

        $this->dispatch('checklistUpdated');
        $this->dispatch('navigateTo', 'dashboard');
    }

    public function confirmDeletion(): void
    {
        $this->confirmingDeletion = true;
    }

    public function closeModal(): void
    {
        $this->confirmingDeletion = false;
        $this->dispatch('modalClosed');
    }

    public function openDeleteModal(): void
    {
        $this->confirmDeletion();
    }

    public function delete(): void
    {
        if (!$this->noteId) {
            $this->dispatch('notification', title: 'Ошибка', content: 'Не удалось найти список', type: 'danger');
            return;
        }

        $checklist = Note::where('user_id', Auth::id())
            ->where('type', Note::TYPE_CHECKLIST)
            ->find($this->noteId);

        if (!$checklist || !$checklist->moveToTrash()) {
            $this->dispatch('notification', title: 'Ошибка', content: 'Не удалось удалить список.', type: 'danger');
            return;
        }

        $this->dispatch('navigateTo', 'dashboard');
    }

    public function saveWithLocation(): void
    {
        if ($this->isSafeSelected($this->folderId)) {
            $this->safeId = $this->folderId;
            $this->folderId = null;
        }

        $this->save();
    }

    public function save(): void
    {
        if (!$this->noteId) {
            return;
        }

        try {
            $this->validateOnly('title');
        } catch (\Illuminate\Validation\ValidationException) {
            $this->dispatch('showError', 'Название обязательно и не должно превышать 255 символов');
            return;
        }

        try {
            if (!$this->cachedChecklist) {
                $this->cachedChecklist = Note::where('user_id', Auth::id())
                    ->where('type', Note::TYPE_CHECKLIST)
                    ->find($this->noteId);
            }

            if (!$this->cachedChecklist) {
                $this->dispatch('notification', title: 'Ошибка', content: 'Список не найден', type: 'danger');
                return;
            }

            $this->updateTitle($this->cachedChecklist);
            $this->updateContent($this->cachedChecklist);
            $this->updateLocation($this->cachedChecklist);

            $this->cachedChecklist->save();

            $this->dispatch('navigateTo', 'dashboard');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('notification', title: 'Ошибка', content: 'Не удалось сохранить список', type: 'danger');
        }
    }

    public function updatedDropdownValue(): void
    {
        // Сохраняем старое местоположение перед изменением
        $oldFolderId = $this->folderId;
        $oldSafeId = $this->safeId;
        $oldArchiveId = $this->archiveId;

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

        // Проверяем, изменилось ли местоположение по сравнению с оригиналом
        $locationChanged = ($this->folderId !== $this->originalFolderId) ||
                           ($this->safeId !== $this->originalSafeId) ||
                           ($this->archiveId !== $this->originalArchiveId);

        $this->autoSave($locationChanged);
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

    #[On('checklistUpdated')]
    public function onChecklistUpdated(): void
    {
        $this->dispatch('navigateTo', 'dashboard');
    }

    #[On('triggerAutoSave')]
    public function autoSave(bool $locationChanged = false): void
    {
        if (!$this->noteId) {
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
            // Перезагружаем из БД если кэш пуст
            if (!$this->cachedChecklist) {
                $this->cachedChecklist = Note::where('user_id', Auth::id())
                    ->where('type', Note::TYPE_CHECKLIST)
                    ->find($this->noteId);
            }

            if (!$this->cachedChecklist) {
                $this->isSaving = false;
                return;
            }

            $this->updateTitle($this->cachedChecklist);
            $this->updateContent($this->cachedChecklist);
            $this->updateLocation($this->cachedChecklist);

            $this->cachedChecklist->save();

            // Показываем уведомление если изменилось местоположение
            if ($locationChanged) {
                $locationName = $this->getLocationName($this->cachedChecklist);
                $this->dispatch('notification', title: 'Обновлено', content: "Место хранения изменено на «{$locationName}»", type: 'success');

                // Обновляем оригинальное местоположение
                $this->originalFolderId = $this->cachedChecklist->folder_id;
                $this->originalSafeId = $this->cachedChecklist->safe_id;
                $this->originalArchiveId = $this->cachedChecklist->archive_id;
            }

            // Можно диспатчить событие для UI, что автосохранение прошло успешно
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

    private function updateTitle(Note $checklist): void
    {
        $checklist->title = trim($this->title);
    }

    private function updateContent(Note $checklist): void
    {
        $checklist->content = $this->content;
    }

    private function updateLocation(Note $checklist): void
    {
        if ($this->folderId !== null) {
            $checklist->folder_id = $this->folderId;
            $checklist->safe_id = null;
            $checklist->archive_id = null;
        } elseif ($this->safeId !== null) {
            $checklist->safe_id = $this->safeId;
            $checklist->folder_id = null;
            $checklist->archive_id = null;
        } elseif ($this->archiveId !== null) {
            $checklist->archive_id = $this->archiveId;
            $checklist->folder_id = null;
            $checklist->safe_id = null;
        } else {
            $checklist->folder_id = null;
            $checklist->safe_id = null;
            $checklist->archive_id = null;
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

    /**
     * Получить название места хранения заметки
     */
    private function getLocationName(Note $note): string
    {
        if ($note->folder_id !== null) {
            $folder = Folder::find($note->folder_id);
            return $folder?->title ?? 'Папка';
        }

        if ($note->safe_id !== null) {
            $safe = Safe::find($note->safe_id);
            return $safe?->name ?? 'Сейф';
        }

        if ($note->archive_id !== null) {
            $archive = Archive::find($note->archive_id);
            return $archive?->name ?? 'Архив';
        }

        return 'Архив';
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
        return view('livewire.edit-checklist');
    }
}