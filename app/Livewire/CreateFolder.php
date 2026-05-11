<?php

namespace App\Livewire;

use App\Dto\CreateFolderDto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;

class CreateFolder extends BaseFolderEditor
{
    public string $heading = 'Создать папку';
    public string $section = 'create-folder';

    /**
     * Сохранить папку
     */
    #[Locked]
    public function save(): void
    {
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

        $dto = new CreateFolderDto(
            userId: Auth::id(),
            title: trim($this->title),
            color: $this->color,
            icon: $this->icon,
        );

        $folder = $this->folderService->createFolder($dto);

        $this->reset(['title', 'color', 'icon']);

        $this->dispatch('notification', [
            'title' => 'Успешно',
            'content' => "Папка «{$folder->title}» успешно создана",
            'type' => 'success'
        ]);

        $this->dispatch('refreshSidebar');
        $this->dispatch('navigateTo', section: 'folder-section', folderId: $folder->id);
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.create-folder');
    }
}
