<?php

namespace App\Livewire;

use App\Services\TrashService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use App\Livewire\Traits\WithModal;

class TrashSection extends Base
{
    use WithModal;

    public string $section = 'trash-section';

    private TrashService $trashService;
    private ?string $subheading = null;
    public $trashedFolders;

    /**
     * Значение сортировки по умолчанию для корзины.
     */
    public string $sort = 'deleted';

    public function boot(TrashService $trashService): void
    {
        $this->trashService = $trashService;
    }

    public function mount(): void
    {
        $this->initPagination(12);
        $this->loadTrashedFolders();
        $this->setSubheading();
    }

    /**
     * Базовые условия для корзины.
     * Условия уже применены в buildNotesQuery(), поэтому возвращаем пустой массив.
     */
    protected function getBaseConditions(): array
    {
        return [];
    }

    /**
     * Общее количество элементов в корзине.
     */
    protected function getTotalCount(): int
    {
        return $this->trashService->getTotalCount(Auth::id());
    }

    /**
     * Установить подзаголовок на основе текущего состояния.
     */
    private function setSubheading(): void
    {
        $this->subheading = match (true) {
            !empty($this->search) => "Результаты поиска: {$this->search}",
            $this->filter !== 'all' => match ($this->filter) {
                'notes' => 'Только заметки',
                'folders' => 'Только папки',
                default => 'Все элементы',
            },
            default => null,
        };
    }

    /**
     * Открыть модальное окно удаления.
     */
    public function deleteItem(int $id, string $type): void
    {
        $this->openModal('delete', [
            'id' => $id,
            'type' => $type,
            'title' => 'Удалить навсегда?',
            'description' => 'Это действие необратимо. Элемент будет удален безвозвратно.'
        ]);
    }

    /**
     * Открыть модальное окно восстановления.
     */
    public function restoreItem(int $id, string $type): void
    {
        $restoreDescription = $this->trashService
            ->getRestoreDescription(Auth::id(), $id, $type);

        $this->openModal('restore', [
            'id' => $id,
            'type' => $type,
            'description' => $restoreDescription
        ]);
    }

    /**
     * Открыть модальное окно восстановления всех элементов.
     */
    public function confirmRestoreAll(): void
    {
        $this->openModal('restoreAll', [
            'title' => 'Восстановить все элементы?',
            'description' => 'Это действие восстановит все удаленные заметки и папки из корзины.'
        ]);
    }

    /**
     * Открыть модальное окно очистки корзины.
     */
    public function confirmEmptyTrash(): void
    {
        $this->openModal('emptyTrash', [
            'title' => 'Очистить корзину?',
            'description' => 'Это действие удалит все элементы из корзины безвозвратно.'
        ]);
    }

    /**
     * Подтвердить восстановление.
     */
    #[Locked]
    public function confirmRestore(): void
    {
        $pendingRestoreId = $this->getModalData('restore', 'id');
        $pendingRestoreType = $this->getModalData('restore', 'type');

        if ($pendingRestoreId === null || $pendingRestoreType === null) {
            $this->closeModal('restore');
            return;
        }

        $result = $pendingRestoreType === 'folder'
            ? $this->trashService->restoreFolder(Auth::id(), $pendingRestoreId)
            : $this->trashService->restoreNote(Auth::id(), $pendingRestoreId);

        if ($result->success) {
            $this->dispatch('notification', [
                'title' => 'Информация',
                'content' => $result->message,
                'type' => 'info',
            ]);
            $this->dispatch('refreshSidebar');

            // Обновляем список папок, если восстановили папку
            if ($pendingRestoreType === 'folder') {
                $this->loadTrashedFolders();
            }
        }

        $this->closeModal('restore');
    }

    /**
     * Подтвердить удаление.
     */
    #[Locked]
    public function confirmDelete(): void
    {
        $pendingDeleteId = $this->getModalData('delete', 'id');
        $pendingDeleteType = $this->getModalData('delete', 'type');

        if ($pendingDeleteId === null || $pendingDeleteType === null) {
            $this->closeModal('delete');
            return;
        }

        $result = $pendingDeleteType === 'folder'
            ? $this->trashService->deleteFolder(Auth::id(), $pendingDeleteId)
            : $this->trashService->deleteNote(Auth::id(), $pendingDeleteId);

        if ($result->success) {
            $this->dispatch('notification', [
                'title' => 'Информация',
                'content' => $result->message,
                'type' => 'info',
            ]);
            $this->dispatch('refreshSidebar');

            // Обновляем список папок, если удалили папку
            if ($pendingDeleteType === 'folder') {
                $this->loadTrashedFolders();
            }
        }

        $this->closeModal('delete');
    }

    /**
     * Подтвердить удаление элемента (метод для вызова из модального окна).
     */
    public function confirmDeleteItem(): void
    {
        $this->confirmDelete();
    }

    /**
     * Подтвердить восстановление всех элементов.
     */
    #[Locked]
    public function restoreAll(): void
    {
        $result = $this->trashService->restoreAll(Auth::id());

        if ($result->success) {
            $this->dispatch('notification', [
                'title' => 'Информация',
                'content' => $result->message,
                'type' => 'info',
            ]);
            $this->dispatch('refreshSidebar');

            // Обновляем список папок
            $this->loadTrashedFolders();
        }

        $this->closeModal('restoreAll');
    }

    /**
     * Подтвердить очистку корзины.
     */
    #[Locked]
    public function emptyTrash(): void
    {
        $result = $this->trashService->emptyTrash(Auth::id());

        if ($result->success) {
            $this->dispatch('notification', [
                'title' => 'Информация',
                'content' => $result->message,
                'type' => 'info',
            ]);
            $this->dispatch('refreshSidebar');

            // Обновляем список папок
            $this->loadTrashedFolders();
        }

        $this->closeModal('emptyTrash');
    }

    #[Computed]
    public function heading(): string
    {
        return 'Корзина';
    }

    #[Computed]
    public function subheading(): ?string
    {
        return $this->subheading ?? null;
    }

    #[Computed]
    public function totalCount(): int
    {
        return $this->notes()->total() + $this->trashedFolders->count();
    }

    #[Computed]
    public function totalTrashCount(): int
    {
        return $this->trashService->getTotalCount(Auth::id());
    }

    /**
     * Проверить, активна ли корзина (есть ли в ней элементы)
     */
    #[Computed]
    public function isTrashActive(): bool
    {
        return $this->trashService->isActive(Auth::id());
    }

    #[Computed]
    public function isRestoreAllModalOpen(): bool
    {
        return $this->isModalOpen('restoreAll');
    }

    #[Computed]
    public function isEmptyTrashModalOpen(): bool
    {
        return $this->isModalOpen('emptyTrash');
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.trash');
    }

    /**
     * Переопределяем buildNotesQuery для корзины.
     * В корзине не используем withTrashed(), а фильтруем по trash_id.
     */
    protected function buildNotesQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = \App\Models\Note::where('user_id', Auth::id())
            ->whereNotNull('trash_id')
            ->whereNull('folder_id')
            ->with('folder');

        // Применяем скоупы (если есть)
        $this->applyScopes($query);

        // Применяем базовые условия
        $this->applyBaseConditions($query);

        return $query;
    }

    /**
     * Загрузить удалённые папки.
     */
    private function loadTrashedFolders(): void
    {
        $this->trashedFolders = $this->trashService->getTrashedFolders(
            Auth::id(),
            $this->search,
            $this->sort
        );
    }

    /**
     * Обновить список элементов при изменении параметров.
     */
    #[On('refreshTrash')]
    public function refreshTrash(): void
    {
        $this->loadTrashedFolders();
        $this->setSubheading();
    }

    /**
     * Обновить данные при изменении свойств.
     */
    public function updated(string $property): void
    {
        parent::updated($property);
        $this->loadTrashedFolders();
        $this->setSubheading();
    }
}
