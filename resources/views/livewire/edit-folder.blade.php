<div>
    <!-- Header Section -->
    <x-header :heading="$this->title" :section='$section' />
    <!-- Content Section -->
    <x-folder-content-section :title="$this->title" :selectedIcon="$this->icon" :color="$this->color" :icons="$this->icons"
        :usedIcons="$this->usedIcons" submitAction="save" saveButtonTarget="save" />
    <!-- Delete Confirmation Modal -->
    <x-modal type="delete" :show="$confirmingDeletion" title="Удалить папку?"
        description="Папка будет перемещена в корзину. Вы сможете восстановить её позже." confirmMethod="deleteFolder"
        cancelMethod="closeModal" />
</div>
