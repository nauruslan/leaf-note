<?php

namespace App\Livewire;

use App\Services\ImageService;
use App\Services\TemporaryImageService;

/**
 * Базовый класс для редакторов заметок
 */
abstract class BaseNoteEditor extends BaseEditor
{
    // Дополнительные сервисы для работы с изображениями
    protected ImageService $imageService;
    protected TemporaryImageService $temporaryImageService;

    /**
     * Инициализация сервисов для работы с изображениями
     */
    public function bootBaseNoteEditor(
        ImageService $imageService,
        TemporaryImageService $temporaryImageService,
    ): void {
        $this->imageService = $imageService;
        $this->temporaryImageService = $temporaryImageService;
    }

    /**
     * Получить сервис изображений
     */
    protected function getImageService(): ImageService
    {
        if (!isset($this->imageService)) {
            $this->imageService = app(ImageService::class);
        }
        return $this->imageService;
    }

    /**
     * Получить сервис временных изображений
     */
    protected function getTemporaryImageService(): TemporaryImageService
    {
        if (!isset($this->temporaryImageService)) {
            $this->temporaryImageService = app(TemporaryImageService::class);
        }
        return $this->temporaryImageService;
    }

    /**
     * Извлечь пути изображений из контента
     */
    protected function extractImagePathsFromContent(mixed $content): array
    {
        return $this->getImageService()->extractImagePaths($content);
    }

    /**
     * Удалить изображения из хранилища
     */
    protected function deleteImagesFromStorage(array $paths): void
    {
        $this->getImageService()->deleteImagesFromStorage($paths);
    }

    /**
     * Выполнить отложенное удаление изображений
     *
     * @param array $currentPaths Пути изображений, которые сейчас в контенте (не удалять)
     * @param int|null $excludeNoteId ID заметки, которую нужно исключить из проверки
     */
    protected function executePendingImageDeletion(array $currentPaths = [], ?int $excludeNoteId = null): void
    {
        $this->getTemporaryImageService()->executePendingDeletion($currentPaths, $excludeNoteId);
    }

    /**
     * Очистить временные изображения
     */
    protected function clearTemporaryImages(): void
    {
        $this->getTemporaryImageService()->clear();
    }

    /**
     * Восстановить все изображения из списка на удаление
     * Используется при отмене редактирования (cancel)
     */
    protected function restoreAllPendingDeletions(): void
    {
        $this->getTemporaryImageService()->restoreAllPendingDeletions();
    }

    /**
     * Удалить изображения, которые были удалены из контента
     *
     * @param array $oldPaths Пути изображений из старого контента
     * @param array $newPaths Пути изображений из нового контента
     * @param int|null $excludeNoteId ID заметки, которую нужно исключить из проверки
     */
    protected function deleteRemovedImages(array $oldPaths, array $newPaths, ?int $excludeNoteId = null): void
    {
        $this->getTemporaryImageService()->deleteRemovedImages($oldPaths, $newPaths, $excludeNoteId);
    }

    /**
     * Удалить несохранённые изображения
     *
     * @param int|null $excludeNoteId ID заметки, которую нужно исключить из проверки
     */
    protected function deleteUnsavedImages(?int $excludeNoteId = null): void
    {
        $this->getTemporaryImageService()->deleteUnsavedImages($excludeNoteId);
    }

    /**
     * Очистить бэкапы изображений, помеченных на удаление
     * Используется при уходе со страницы редактирования/создания заметки
     */
    protected function cleanupPendingBackups(): void
    {
        $this->getTemporaryImageService()->cleanupPendingBackups($this->noteId);
    }

}