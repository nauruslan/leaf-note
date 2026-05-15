<?php

namespace App\Livewire\Traits;

/**
 * Универсальный трейт для управления модальными окнами
 */
trait WithModal
{
    /**
     * Массив для хранения состояний всех модальных окон
     * Ключ - имя модального окна, значение - состояние (bool)
     */
    public array $modals = [];

    /**
     * Массив для хранения дополнительных данных модальных окон
     * Ключ - имя модального окна, значение - массив данных
     */
    public array $modalData = [];

    /**
     * Открыть модальное окно
     *
     * @param string $modalName Имя модального окна
     * @param array $data Дополнительные данные для модального окна
     * @return void
     */
    public function openModal(string $modalName, array $data = []): void
    {
        $this->modals[$modalName] = true;

        if (!empty($data)) {
            $this->modalData[$modalName] = $data;
        }

        if (method_exists($this, 'dispatch')) {
            $this->dispatch('modalOpened', modal: $modalName);
        }
    }

    /**
     * Закрыть модальное окно
     *
     * @param string $modalName Имя модального окна
     * @return void
     */
    public function closeModal(string $modalName = null): void
    {
        if ($modalName === null) {
            // Закрыть все модальные окна
            $this->modals = [];
            $this->modalData = [];
        } else {
            // Закрыть конкретное модальное окно
            $this->modals[$modalName] = false;
            unset($this->modalData[$modalName]);
        }

        if (method_exists($this, 'dispatch')) {
            $this->dispatch('modalClosed', modal: $modalName);
        }
    }

    /**
     * Переключить состояние модального окна
     *
     * @param string $modalName Имя модального окна
     * @param array $data Дополнительные данные для модального окна
     * @return void
     */
    public function toggleModal(string $modalName, array $data = []): void
    {
        if ($this->isModalOpen($modalName)) {
            $this->closeModal($modalName);
        } else {
            $this->openModal($modalName, $data);
        }
    }

    /**
     * Проверить, открыто ли модальное окно
     *
     * @param string $modalName Имя модального окна
     * @return bool
     */
    public function isModalOpen(string $modalName): bool
    {
        return $this->modals[$modalName] ?? false;
    }

    /**
     * Получить данные модального окна
     *
     * @param string $modalName Имя модального окна
     * @param string $key Ключ данных
     * @param mixed $default Значение по умолчанию
     * @return mixed
     */
    public function getModalData(string $modalName, string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->modalData[$modalName] ?? [];
        }

        return $this->modalData[$modalName][$key] ?? $default;
    }

    /**
     * Установить данные для модального окна
     *
     * @param string $modalName Имя модального окна
     * @param string|array $key Ключ данных или массив данных
     * @param mixed $value Значение (если $key - строка)
     * @return void
     */
    public function setModalData(string $modalName, $key, $value = null): void
    {
        if (is_array($key)) {
            $this->modalData[$modalName] = $key;
        } else {
            $this->modalData[$modalName][$key] = $value;
        }
    }

    /**
     * Открыть модальное окно подтверждения удаления
     *
     * @param int|null $id ID элемента для удаления
     * @param string|null $type Тип элемента
     * @param string $title Заголовок модального окна
     * @param string $description Описание в модальном окне
     * @return void
     */
    public function confirmDelete(?int $id = null, ?string $type = null, string $title = '', string $description = ''): void
    {
        $this->openModal('delete', [
            'id' => $id,
            'type' => $type,
            'title' => $title,
            'description' => $description
        ]);
    }

    /**
     * Открыть модальное окно подтверждения
     *
     * @param string $title Заголовок модального окна
     * @param string $description Описание в модальном окне
     * @param array $data Дополнительные данные
     * @return void
     */
    public function confirm(string $title = '', string $description = '', array $data = []): void
    {
        $this->openModal('confirm', array_merge([
            'title' => $title,
            'description' => $description
        ], $data));
    }

    /**
     * Открыть информационное модальное окно
     *
     * @param string $title Заголовок модального окна
     * @param string $description Описание в модальном окне
     * @param array $data Дополнительные данные
     * @return void
     */
    public function info(string $title = '', string $description = '', array $data = []): void
    {
        $this->openModal('info', array_merge([
            'title' => $title,
            'description' => $description
        ], $data));
    }

    /**
     * Получить ID элемента для удаления из модального окна
     *
     * @return int|null
     */
    public function getPendingDeleteId(): ?int
    {
        return $this->getModalData('delete', 'id');
    }

    /**
     * Получить тип элемента для удаления из модального окна
     *
     * @return string|null
     */
    public function getPendingDeleteType(): ?string
    {
        return $this->getModalData('delete', 'type');
    }

    /**
     * Получить заголовок модального окна
     *
     * @param string $modalName Имя модального окна
     * @return string
     */
    public function getModalTitle(string $modalName): string
    {
        return $this->getModalData($modalName, 'title', '');
    }

    /**
     * Получить описание модального окна
     *
     * @param string $modalName Имя модального окна
     * @return string
     */
    public function getModalDescription(string $modalName): string
    {
        return $this->getModalData($modalName, 'description', '');
    }
}
