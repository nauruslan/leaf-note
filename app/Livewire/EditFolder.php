<?php

namespace App\Livewire;

use App\Dto\UpdateFolderDto;
use App\Livewire\Traits\WithModal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;

class EditFolder extends BaseFolderEditor
{
    use WithModal;

    public string $heading = 'Редактирование папки';
    public string $section = 'edit-folder';

    // Исходные значения для сравнения
    protected string $originalTitle = '';
    protected string $originalColor = '';
    protected string $originalIcon = '';

    /**
     * Инициализация компонента
     */
    public function mount(?int $folderId = null): void
    {
        $this->folderId = $folderId;

        if ($this->folderId) {
            $folder = $this->folderService->getFolder(Auth::id(), $this->folderId);

            if (!$folder) {
                $this->dispatch('notification', [
                    'title' => 'Ошибка',
                    'content' => 'Папка не найдена или у вас нет прав на её редактирование.',
                    'type' => 'danger'
                ]);
                $this->dispatch('navigateTo', section: 'dashboard-section');
                return;
            }

            $this->title = $folder->title;
            $this->color = $folder->color;
            $this->icon = $folder->icon;

            $this->initOriginalValues();
        }
    }

    /**
     * Инициализация оригинальных значений
     */
    protected function initOriginalValues(): void
    {
        $this->originalTitle = $this->title;
        $this->originalColor = $this->color;
        $this->originalIcon = $this->icon;
    }

    /**
     * Проверить наличие изменений
     */
    protected function hasChanges(): bool
    {
        return $this->originalTitle !== $this->title
            || $this->originalColor !== $this->color
            || $this->originalIcon !== $this->icon;
    }

    /**
     * Сохранить папку
     */
    #[Locked]
    public function save(): void
    {
        if (!$this->hasChanges()) {
            $this->dispatch('notification', [
                'title' => 'Информация',
                'content' => 'Нет изменений для сохранения',
                'type' => 'info'
            ]);
            return;
        }

        try {
            $this->validate($this->getValidationRules(), $this->getValidationMessages());
        } catch (ValidationException $e) {
            $this->dispatch('notification', [
                'title' => 'Внимание',
                'content' => 'Пожалуйста, исправьте ошибки в форме',
                'type' => 'warning'
            ]);
            throw $e;
        }

        $dto = new UpdateFolderDto(
            userId: Auth::id(),
            folderId: $this->folderId,
            title: trim($this->title),
            color: $this->color,
            icon: $this->icon,
        );

        $this->folderService->updateFolder($dto);

        $this->initOriginalValues();

        $this->dispatch('notification', [
            'title' => 'Успешно',
            'content' => 'Изменения сохранены',
            'type' => 'info'
        ]);

        $this->dispatch('refreshSidebar');
    }

    /**
     * Отменить изменения
     */
    public function cancel(): void
    {
        $this->back();
    }

    /**
     * Открыть модальное окно удаления
     */
    public function openDeleteModal(): void
    {
        $this->confirmDelete($this->folderId, 'folder', 'Удалить папку?', 'Папка будет перемещена в корзину. Вы сможете восстановить её позже.');
    }

    /**
     * Удалить папку
     */
    #[Locked]
    public function deleteFolder(): void
    {
        $result = $this->folderService->deleteFolder(Auth::id(), $this->folderId);

        if (!$result['success']) {
            $this->dispatch('notification', [
                'title' => 'Ошибка',
                'content' => $result['message'],
                'type' => 'danger'
            ]);
            $this->closeModal('delete');
            return;
        }

        $this->dispatch('notification', [
            'title' => 'Удалено',
            'content' => $result['message'],
            'type' => 'danger'
        ]);

        $this->closeModal('delete');
        $this->dispatch('navigateTo', section: 'dashboard-section');
        $this->dispatch('refreshSidebar');
    }

    /**
     * Проверить, открыто ли модальное окно
     */
    #[Computed]
    public function isModalOpen(string $modalName): bool
    {
        return $this->modals[$modalName] ?? false;
    }

    /**
     * Получить данные модального окна
     */
    #[Computed]
    public function getModalData(string $modalName, string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->modalData[$modalName] ?? [];
        }

        return $this->modalData[$modalName][$key] ?? $default;
    }

    /**
     * Получить заголовок модального окна
     */
    #[Computed]
    public function getModalTitle(string $modalName): string
    {
        return $this->getModalData($modalName, 'title', '');
    }

    /**
     * Получить описание модального окна
     */
    #[Computed]
    public function getModalDescription(string $modalName): string
    {
        return $this->getModalData($modalName, 'description', '');
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.edit-folder');
    }
}