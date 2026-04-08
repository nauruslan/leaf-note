<?php

namespace App\Livewire;

use App\Livewire\Traits\WithComponentPagination;
use App\Livewire\Traits\WithFiltering;
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
    use WithFiltering;

    public $section='trash';

    // Свойства для модального окна подтверждения удаления
    public bool $confirmingDeletion = false;
    public ?int $pendingDeleteId = null;
    public ?string $pendingDeleteType = null;

    // Свойства для модального окна подтверждения восстановления
    public bool $confirmingRestore = false;
    public ?int $pendingRestoreId = null;
    public ?string $pendingRestoreType = null;
    public string $restoreDescription = '';

    // Свойства для модальных окон восстановления всех и очистки
    public bool $confirmingRestoreAll = false;
    public bool $confirmingEmptyTrash = false;

    #[On('restoreItem')]
    public function handleRestoreItem(int $id, string $type): void
    {
        $this->confirmingRestore = true;
        $this->pendingRestoreId = $id;
        $this->pendingRestoreType = $type;

        if ($type === 'folder') {
            $this->restoreDescription = 'Папка будет восстановлена';
        } else {
            $note = Note::find($id);
            $isChecklist = $note && $note->type === 'checklist';
            $this->restoreDescription = $isChecklist
                ? 'Список будет перемещен в архив'
                : 'Заметка будет перемещена в архив';
        }
    }

    #[On('deleteItem')]
    public function handleDeleteItem(int $id, string $type): void
    {
        $this->confirmingDeletion = true;
        $this->pendingDeleteId = $id;
        $this->pendingDeleteType = $type;
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

    public function closeModal(): void
    {
        $this->confirmingDeletion = false;
        $this->pendingDeleteId = null;
        $this->pendingDeleteType = null;
        $this->confirmingRestore = false;
        $this->pendingRestoreId = null;
        $this->pendingRestoreType = null;
        $this->restoreDescription = '';
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

    // Заметки, принадлежащие папкам, отображаются внутри папок
    #[Computed]
    public function trashedNotes(): LengthAwarePaginator
    {
        $sortMap = [
            'deleted' => 'moved_to_trash_at',
            'title' => 'title',
        ];
        $sortDirections = [
            'deleted' => 'desc',
            'title' => 'asc',
        ];

        $query = Note::where('user_id', Auth::id())
            ->whereNotNull('trash_id')
            ->whereNull('folder_id')
            ->with('folder');

        // Применяем поиск
        $query = $this->applySearch($query, ['title', 'payload']);

        // Применяем сортировку
        $query = $this->applySort($query, $sortMap, $sortDirections);

        return $query->paginate($this->perPage, ['*'], 'page', $this->page);
    }

    #[Computed]
    public function trashedFolders()
    {
        $sortMap = [
            'deleted' => 'moved_to_trash_at',
            'title' => 'title',
        ];
        $sortDirections = [
            'deleted' => 'desc',
            'title' => 'asc',
        ];

        $query = Folder::where('user_id', Auth::id())
            ->whereNotNull('trash_id');

        // Применяем поиск
        $query = $this->applySearch($query, ['title']);

        // Применяем сортировку
        $query = $this->applySort($query, $sortMap, $sortDirections);

        return $query->get();
    }

    // Общее количество элементов в корзине.
    #[Computed]
    public function totalCount(): int
    {
        // Считаем только заметки без папки
        $notesCount = Note::where('user_id', Auth::id())
            ->whereNotNull('trash_id')
            ->whereNull('folder_id')
            ->count();

        // Каждая папка - один элемент (даже если в ней много заметок)
        $foldersCount = Folder::where('user_id', Auth::id())
            ->whereNotNull('trash_id')
            ->count();

        return $notesCount + $foldersCount;
    }

    // Очистить корзину - удалить все заметки и папки безвозвратно.
    public function emptyTrash(): void
    {
        $userId = Auth::id();

        // Сначала удаляем все папки в корзине (их заметки удалятся через Folder::deleting)
        Folder::where('user_id', $userId)
            ->whereNotNull('trash_id')
            ->delete();

        // Затем удаляем заметки без папки
        Note::where('user_id', $userId)
            ->whereNotNull('trash_id')
            ->whereNull('folder_id')
            ->delete();

        // Сбрасываем счётчик корзины
        $trash = Auth::user()->trash;
        $trash->resetQuantity();
        $trash->save();

        // Обновляем сайдбар
        $this->dispatch('refreshSidebar');
    }

    // Восстановить всё - заметки в архив, папки восстанавливаются вместе с заметками.
    public function restoreAll(): void
    {
        $user = Auth::user();
        $archive = $user->archive;
        $trash = $user->trash;

        // Сначала восстанавливаем все папки (они восстановят свои заметки)
        $folders = Folder::where('user_id', $user->id)
            ->whereNotNull('trash_id')
            ->get();

        foreach ($folders as $folder) {
            $folder->restoreFromTrash();
        }

        // Затем восстанавливаем заметки (без папки) в архив
        $orphanNotes = Note::where('user_id', $user->id)
            ->whereNotNull('trash_id')
            ->whereNull('folder_id')
            ->get();

        foreach ($orphanNotes as $note) {
            $note->update([
                'trash_id' => null,
                'archive_id' => $archive->id,
                'moved_to_trash_at' => null,
            ]);
        }

        // Сбрасываем счётчик корзины
        $trash->resetQuantity();
        $trash->save();

        // Обновляем сайдбар
        $this->dispatch('refreshSidebar');
    }

    // Восстановить одну заметку.
    public function restoreNote(int $noteId): void
    {
        $note = Note::where('user_id', Auth::id())->find($noteId);

        if (!$note || !$note->isInTrash()) {
            return;
        }

        $note->restoreFromTrash();
        $this->dispatch('refreshSidebar');
    }

    // Восстановить одну папку.
    public function restoreFolder(int $folderId): void
    {
        $folder = Folder::where('user_id', Auth::id())->find($folderId);

        if (!$folder || !$folder->isInTrash()) {
            return;
        }

        // Восстанавливаем папку и её заметки
        $folder->restoreFromTrash();

        $this->dispatch('refreshSidebar');
    }


    public function deleteNote(int $noteId): void
    {
        $note = Note::where('user_id', Auth::id())->find($noteId);

        if (!$note || !$note->isInTrash()) {
            return;
        }

        $note->delete();

        $trash = Auth::user()->trash;
        $trash->decrementQuantity();
        $trash->save();

        $this->dispatch('refreshSidebar');
    }

    public function deleteFolder(int $folderId): void
    {
        $folder = Folder::where('user_id', Auth::id())->find($folderId);

        if (!$folder || !$folder->isInTrash()) {
            return;
        }

        $notesToDelete = Note::where('folder_id', $folder->id)
            ->whereNotNull('trash_id')
            ->count();

        Note::where('folder_id', $folder->id)
            ->whereNotNull('trash_id')
            ->delete();

        $folder->delete();

        $trash = Auth::user()->trash;
        $trash->decrementQuantity(1 + $notesToDelete);
        $trash->save();

        $this->dispatch('refreshSidebar');
    }

    public function render()
    {
        return view('livewire.trash');
    }
}
