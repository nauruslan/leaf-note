<?php

namespace App\Livewire\Traits;

trait WithFolderOpening
{
   public function openFolder(int $folderId): void
    {
        if (!$folderId) {
            return;
        }

        $this->dispatch('navigateTo', section: 'folder', folderId: $folderId);
    }
}
