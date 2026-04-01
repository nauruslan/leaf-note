<?php

namespace App\Livewire;

use App\Livewire\Traits\WithComponentPagination;
use App\Livewire\Traits\WithSearch;
use App\Models\Folder;
use App\Models\Note;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class TrashView extends Component
{
    use WithComponentPagination;
    use WithSearch;

    #[On('restoreItem')]
    public function handleRestoreItem(int $id, string $type): void
    {
        $this->confirmingRestore = true;
        $this->pendingRestoreId = $id;
        $this->pendingRestoreType = $type;
    }

    public function closeModal(): void
    {
        $this->confirmingDeletion = false;
        $this->pendingDeleteId = null;
        $this->pendingDeleteType = null;
        $this->confirmingRestore = false;
        $this->pendingRestoreId = null;
        $this->pendingRestoreType = null;
    }

    public function confirmRestore(): void
    {
        if ($this->pendingRestoreId === null || $this->pendingRestoreType === null) {
            $this->closeModal();
            return;
        }

        if ($this->pendingRestoreType === 'folder') {
            $this->restoreFolder($this->pendingRestoreId);
        } else {
            $this->restoreNote($this->pendingRestoreId);
        }

        $this->closeModal();
    }

    #[On('deleteItem')]
    public function handleDeleteItem(int $id, string $type): void
    {
        $this->confirmingDeletion = true;
        $this->pendingDeleteId = $id;
        $this->pendingDeleteType = $type;
    }

    public function confirmDelete(): void
    {
        if ($this->pendingDeleteId === null || $this->pendingDeleteType === null) {
            $this->closeModal();
            return;
        }

        if ($this->pendingDeleteType === 'folder') {
            $this->deleteFolder($this->pendingDeleteId);
        } else {
            $this->deleteNote($this->pendingDeleteId);
        }

        $this->closeModal();
    }

    #[On('confirmRestoreAll')]
    public function handleConfirmRestoreAll(): void
    {
        $this->confirmingRestoreAll = true;
    }

    public function closeRestoreAllModal(): void
    {
        $this->confirmingRestoreAll = false;
    }

    #[On('confirmEmptyTrash')]
    public function handleConfirmEmptyTrash(): void
    {
        $this->confirmingEmptyTrash = true;
    }

    public function closeEmptyTrashModal(): void
    {
        $this->confirmingEmptyTrash = false;
    }

    public string $search = '';
    public string $filter = 'all';
    public string $sort = 'deleted';

    // Свойства для модального окна подтверждения удаления
    public bool $confirmingDeletion = false;
    public ?int $pendingDeleteId = null;
    public ?string $pendingDeleteType = null;

    // Свойства для модального окна подтверждения восстановления
    public bool $confirmingRestore = false;
    public ?int $pendingRestoreId = null;
    public ?string $pendingRestoreType = null;

    // Свойства для модальных окон восстановления всех и очистки
    public bool $confirmingRestoreAll = false;
    public bool $confirmingEmptyTrash = false;

    /**
     * Получить все заметки в корзине.
     */
    #[Computed]
    public function trashedNotes(): LengthAwarePaginator
    {
        $query = Note::where('user_id', Auth::id())
            ->whereNotNull('trash_id')
            ->with('folder');

        // Применяем поиск
        $query = $this->applySearch($query, ['title', 'payload']);

        // Применяем сортировку
        $sortMap = [
            'deleted' => ['column' => 'moved_to_trash_at', 'direction' => 'desc'],
            'title' => ['column' => 'title', 'direction' => 'asc'],
        ];

        $sortConfig = $sortMap[$this->sort] ?? $sortMap['deleted'];
        $query->orderBy($sortConfig['column'], $sortConfig['direction']);

        return $query->paginate($this->perPage, ['*'], 'page', $this->page);
    }

    /**
     * Получить все папки в корзине.
     */
    #[Computed]
    public function trashedFolders()
    {
        $query = Folder::where('user_id', Auth::id())
            ->whereNotNull('trash_id');

        // Применяем поиск
        if (!empty($this->search)) {
            $query->where('title', 'like', '%' . $this->search . '%');
        }

        // Применяем сортировку
        $sortMap = [
            'deleted' => ['column' => 'moved_to_trash_at', 'direction' => 'desc'],
            'title' => ['column' => 'title', 'direction' => 'asc'],
        ];

        $sortConfig = $sortMap[$this->sort] ?? $sortMap['deleted'];
        $query->orderBy($sortConfig['column'], $sortConfig['direction']);

        return $query->get();
    }

    /**
     * Общее количество элементов в корзине.
     */
    #[Computed]
    public function totalCount(): int
    {
        $notesCount = Note::where('user_id', Auth::id())
            ->whereNotNull('trash_id')
            ->count();

        $foldersCount = Folder::where('user_id', Auth::id())
            ->whereNotNull('trash_id')
            ->count();

        return $notesCount + $foldersCount;
    }

    /**
     * Сбросить пагинацию при изменении параметров.
     */
    public function updated($property): void
    {
        if (in_array($property, ['search', 'filter', 'sort'])) {
            $this->resetPagination();
        }
    }

    /**
     * Очистить корзину - удалить все заметки и папки безвозвратно.
     */
    public function emptyTrash(): void
    {
        $userId = Auth::id();

        // Сначала удаляем все заметки в корзине (включая те, что в папках)
        Note::where('user_id', $userId)
            ->whereNotNull('trash_id')
            ->delete();

        // Затем удаляем все папки в корзине
        Folder::where('user_id', $userId)
            ->whereNotNull('trash_id')
            ->delete();

        // Сбрасываем счётчик корзины
        $trash = Auth::user()->trash;
        $trash->resetQuantity();
        $trash->save();

        // Обновляем сайдбар
        $this->dispatch('refreshSidebar');
    }

    /**
     * Восстановить всё - заметки в архив, папки в корень.
     */
    public function restoreAll(): void
    {
        $user = Auth::user();
        $archive = $user->archive;
        $trash = $user->trash;

        // Восстанавливаем все заметки в архив
        $notes = Note::where('user_id', $user->id)
            ->whereNotNull('trash_id')
            ->get();

        foreach ($notes as $note) {
            $note->update([
                'trash_id' => null,
                'archive_id' => $archive->id,
                'moved_to_trash_at' => null,
            ]);
        }

        // Восстанавливаем все папки (без родителя)
        $folders = Folder::where('user_id', $user->id)
            ->whereNotNull('trash_id')
            ->get();

        foreach ($folders as $folder) {
            $folder->update([
                'trash_id' => null,
                'moved_to_trash_at' => null,
            ]);

            // Перемещаем заметки папки в архив
            $folderNotes = Note::where('folder_id', $folder->id)
                ->whereNotNull('trash_id')
                ->get();

            foreach ($folderNotes as $note) {
                $note->update([
                    'folder_id' => null,
                    'trash_id' => null,
                    'archive_id' => $archive->id,
                    'moved_to_trash_at' => null,
                ]);
            }
        }

        // Сбрасываем счётчик корзины
        $trash->resetQuantity();
        $trash->save();

        // Обновляем сайдбар
        $this->dispatch('refreshSidebar');
    }

    /**
     * Восстановить одну заметку.
     */
    public function restoreNote(int $noteId): void
    {
        $note = Note::where('user_id', Auth::id())->find($noteId);

        if (!$note || !$note->isInTrash()) {
            return;
        }

        $note->restoreFromTrash();
        $this->dispatch('refreshSidebar');
    }

    /**
     * Восстановить одну папку.
     */
    public function restoreFolder(int $folderId): void
    {
        $folder = Folder::where('user_id', Auth::id())->find($folderId);

        if (!$folder || !$folder->isInTrash()) {
            return;
        }

        $user = Auth::user();
        $archive = $user->archive;

        // Восстанавливаем папку
        $folder->restoreFromTrash();

        // Восстанавливаем заметки папки в архив
        $notes = Note::where('folder_id', $folder->id)
            ->whereNotNull('trash_id')
            ->get();

        foreach ($notes as $note) {
            $note->update([
                'folder_id' => null,
                'trash_id' => null,
                'archive_id' => $archive->id,
                'moved_to_trash_at' => null,
            ]);
        }

        $this->dispatch('refreshSidebar');
    }

    /**
     * Удалить одну заметку безвозвратно.
     */
    public function deleteNote(int $noteId): void
    {
        $note = Note::where('user_id', Auth::id())->find($noteId);

        if (!$note || !$note->isInTrash()) {
            return;
        }

        $note->delete();

        // Обновляем счётчик корзины
        $trash = Auth::user()->trash;
        $trash->decrementQuantity();
        $trash->save();

        $this->dispatch('refreshSidebar');
    }

    /**
     * Удалить одну папку безвозвратно.
     */
    public function deleteFolder(int $folderId): void
    {
        $folder = Folder::where('user_id', Auth::id())->find($folderId);

        if (!$folder || !$folder->isInTrash()) {
            return;
        }

        // Удаляем заметки папки, которые находятся в корзине
        // (folder_id обнуляется при перемещении в корзину, но trash_id сохраняется)
        $notesToDelete = Note::where('folder_id', $folder->id)
            ->whereNotNull('trash_id')
            ->count();

        Note::where('folder_id', $folder->id)
            ->whereNotNull('trash_id')
            ->delete();

        // Удаляем папку
        $folder->delete();

        // Обновляем счётчик корзины: папка + все её заметки
        $trash = Auth::user()->trash;
        $trash->decrementQuantity(1 + $notesToDelete);
        $trash->save();

        $this->dispatch('refreshSidebar');
    }

    /**
     * Перейти на другую секцию (используется для кнопки "Вернуться на главную").
     */
    public function goTo(string $section, ?int $folderId = null): void
    {
        $this->dispatch('navigateTo', section: $section, folderId: $folderId);
    }

    public function render()
    {
        return view('livewire.trash');
    }
}
