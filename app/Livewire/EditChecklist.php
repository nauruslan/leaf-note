<?php

namespace App\Livewire;

use App\Livewire\Traits\WithFavorite;
use App\Livewire\Traits\WithFolderSafeSelection;
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

    public $heading='Создать заметку';

    private const EMPTY_CHECKLIST_STRUCTURE = '{"type":"doc","content":[{"type":"checklist","content":[]}]}';

    public ?int $noteId = null;
    public string $title = '';
    public ?int $folderId = null;
    public ?int $safeId = null;
    public string $content = '';
    public bool $confirmingDeletion = false;
    public bool $isSaving = false;
    public bool $is_favorite = false;

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
        $this->is_favorite = (bool) $this->cachedChecklist->is_favorite;
        $this->content = $this->normalizeContent($this->cachedChecklist->payload);

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

    public function toggleDeleteModal(): void
    {
        if ($this->noteId === null) {
            $this->dispatch('notification', title: 'Ошибка', content: 'Не удалось найти список для удаления', type: 'danger');
            return;
        }

        $this->confirmingDeletion = ! $this->confirmingDeletion;
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
            // Перезагружаем из БД если кэш пуст (например, при пересоздании компонента)
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

    public function updatedFolderId(): void
    {
        $this->autoSave();
    }

    #[On('updateSafeId')]
    public function setSafeId(int $id): void
    {
        $this->safeId = $id;
        $this->folderId = null;
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
    public function autoSave(): void
    {
        if (!$this->noteId) {
            return;
        }

        // Если выбранный folderId является сейфом, перемещаем его в safeId
        if ($this->folderId && $this->isSafeSelected($this->folderId)) {
            $this->safeId = $this->folderId;
            $this->folderId = null;
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

            // Можно диспатчить событие для UI, что автосохранение прошло успешно
            // $this->dispatch('autosaveCompleted');
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
        $checklist->payload = $this->content;
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

        return Safe::where('user_id', Auth::id())->where('id', $selectedId)->exists();
    }

    public function back(): void
    {
        $previousSection = StateManager::get('previous_section', 'dashboard');
        $previousFolderId = StateManager::get('previous_folderId');
        $previousNoteId = StateManager::get('previous_noteId');

        $this->dispatch('navigateTo', $previousSection, $previousFolderId, $previousNoteId);
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.edit-checklist');
    }
}
