<?php

namespace App\Livewire;

use App\Livewire\Traits\WithBackSection;
use App\Livewire\Traits\WithFavorite;
use App\Livewire\Traits\WithNoteStore;
use App\Models\Archive;
use App\Models\Folder;
use App\Models\Note;
use App\Models\Safe;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class EditChecklist extends Component
{
    use WithBackSection;
    use WithNoteStore;
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

    public function mount(?int $noteId = null, ?int $folderId = null)
    {
        $this->noteId = $noteId;

        if ($noteId === null) {
            $this->dispatch('notification', ['title' => 'Ошибка', 'content' => 'Заметка не найдена', 'type' => 'danger']);
            $this->dispatch('navigateTo', section:'dashboard');
            return;
        }

        $this->cachedChecklist = Note::where('user_id', Auth::id())
            ->where('type', Note::TYPE_CHECKLIST)
            ->find($noteId);

        if (!$this->cachedChecklist) {
            $this->dispatch('notification', ['title' => 'Ошибка', 'content' => 'Список не найден', 'type' => 'danger']);
            $this->dispatch('navigateTo', section:'dashboard');
            return;
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

    public function confirmDelete(): void
    {
        $checklist = $this->checklist();

        if (!$checklist) {
            $this->dispatch('notification', ['title' => 'Ошибка', 'content' => 'Список не найден', 'type' => 'danger']);
            return;
        }

        if ($checklist->is_favorite) {
            $checklist->toggleFavorite();
        }

        if (!$checklist->moveToTrash()) {
            // Корзина переполнена
            $this->dispatch('notification', ['title' => 'Ошибка', 'content' => 'Корзина переполнена. Очистите корзину перед удалением.', 'type' => 'danger']);
            return;
        }

        $this->dispatch('notification', ['title' => 'Удалено', 'content' => "Список «{$checklist->title}» отправлен в корзину", 'type' => 'danger']);
        $this->dispatch('navigateTo', 'dashboard');
        // Обновляем sidebar
        $this->dispatch('refreshSidebar');
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
            $this->dispatch('notification', ['title' => 'Ошибка', 'content' => 'Не удалось найти список', 'type' => 'danger']);
            return;
        }

        $checklist = Note::where('user_id', Auth::id())
            ->where('type', Note::TYPE_CHECKLIST)
            ->find($this->noteId);

        if (!$checklist || !$checklist->moveToTrash()) {
            $this->dispatch('notification', ['title' => 'Ошибка', 'content' => 'Не удалось удалить список.', 'type' => 'danger']);
            return;
        }

        $this->dispatch('navigateTo', 'dashboard');
        // Обновляем sidebar
        $this->dispatch('refreshSidebar');
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

        // Проверка уникальности title (исключая текущий список)
        if ($this->isTitleExists(trim($this->title), $this->noteId)) {
            $this->dispatch('notification', ['title' => 'Внимание', 'content' => 'Список с таким названием уже есть. Чтобы избежать путаницы измените название.', 'type' => 'warning']);
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
                $this->dispatch('notification', ['title' => 'Обновлено', 'content' => "Место хранения изменено на «{$locationName}»", 'type' => 'info']);

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
            // Обновляем sidebar
            $this->dispatch('refreshSidebar');
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

    /**
     * Проверить существование списка с указанным названием у текущего пользователя
     */
    private function isTitleExists(string $title, ?int $excludeNoteId = null): bool
    {
        $query = Note::where('user_id', Auth::id())
            ->where('type', Note::TYPE_CHECKLIST)
            ->where('title', $title)
            ->whereNull('trash_id');

        if ($excludeNoteId) {
            $query->where('id', '!=', $excludeNoteId);
        }

        return $query->exists();
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.edit-checklist');
    }
}
