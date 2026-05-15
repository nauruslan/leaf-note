<div>
    <!-- Header Section -->
    <x-header :heading="$this->checklist->title" :section='$section' />
    <!-- ControlPanel Section -->
    <x-checklist-editor-control-panel :folders="$this->folders" :safes="$this->safes" :archives="$this->archives" :dropdownValue="$dropdownValue"
        :folderId="$folderId" :safeId="$safeId" :archiveId="$archiveId" :is_favorite="$is_favorite" />
    <!-- Content Section -->
    <x-checklist-editor-content-section :title="$title" :content="$content" :checklist="$this->checklist"
        editorId="edit-checklist-editor" contentInputId="checklist-content-input" contentDebounce="500ms" />
    <!-- Delete Confirmation Modal -->
    <x-modal type="delete" :show="$this->isModalOpen('delete')" :title="$this->getModalTitle('delete')" :description="$this->getModalDescription('delete')" confirmMethod="confirmDeletion"
        cancelMethod="closeModal('delete')" />
</div>
