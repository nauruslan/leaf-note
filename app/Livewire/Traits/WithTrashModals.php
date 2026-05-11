<?php

namespace App\Livewire\Traits;

/**
 * Трейт для управления модальными окнами корзины
 */
trait WithTrashModals
{
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

    /**
     * Открыть модальное окно удаления
     */
    public function deleteItem(int $id, string $type): void
    {
        $this->confirmingDeletion = true;
        $this->pendingDeleteId = $id;
        $this->pendingDeleteType = $type;
    }

    /**
     * Открыть модальное окно восстановления
     */
    public function restoreItem(int $id, string $type): void
    {
        $this->confirmingRestore = true;
        $this->pendingRestoreId = $id;
        $this->pendingRestoreType = $type;

        $this->restoreDescription = app(\App\Services\TrashService::class)
            ->getRestoreDescription(\Illuminate\Support\Facades\Auth::id(), $id, $type);
    }

    /**
     * Открыть модальное окно восстановления всех
     */
    public function confirmRestoreAll(): void
    {
        if ($this->totalCount === 0) {
            return;
        }
        $this->confirmingRestoreAll = true;
    }

    /**
     * Закрыть модальное окно восстановления всех
     */
    public function closeRestoreAllModal(): void
    {
        $this->confirmingRestoreAll = false;
        $this->dispatch('modalClosed');
    }

    /**
     * Открыть модальное окно очистки корзины
     */
    public function confirmEmptyTrash(): void
    {
        if ($this->totalCount === 0) {
            return;
        }
        $this->confirmingEmptyTrash = true;
    }

    /**
     * Закрыть модальное окно очистки корзины
     */
    public function closeEmptyTrashModal(): void
    {
        $this->confirmingEmptyTrash = false;
        $this->dispatch('modalClosed');
    }

    /**
     * Закрыть все модальные окна
     */
    public function closeModal(): void
    {
        $this->confirmingDeletion = false;
        $this->pendingDeleteId = null;
        $this->pendingDeleteType = null;
        $this->confirmingRestore = false;
        $this->pendingRestoreId = null;
        $this->pendingRestoreType = null;
        $this->restoreDescription = '';
        $this->dispatch('modalClosed');
    }
}