<?php

namespace App\Livewire\Traits;

trait WithFolderOpening
{
   public function openFolder(int $folderId): void
    {
        if (!$folderId) {
            return;
        }

        // Обновляем активную секцию в навигации (глобальное событие)
        $this->js("Livewire.dispatch('stateUpdated', {section: 'folder', folderId: {$folderId}})");
        // Навигируем к папке
        $this->dispatch('navigateTo', section: 'folder', folderId: $folderId);
    }
}