<div>
    <!-- Header Section -->
    <x-header :heading="$title" :section="$section" />
    <!-- ControlPanel Section -->
    <x-note-editor-control-panel :folders="$this->folders" :safes="$this->safes" :archives="$this->archives" :dropdownValue="$dropdownValue"
        :folderId="$folderId" :safeId="$safeId" :archiveId="$archiveId" :is_favorite="$is_favorite" />
    <!-- Content Section -->
    <x-note-editor-content-section :title="$title" :content="$content" :note="$this->note" editorId="note-view-editor"
        imageUploadInputId="note-view-image-upload-input" contentInputId="note-view-content-input" />
    <!-- Delete Confirmation Modal -->
    <x-modal type="delete" :show="$this->isModalOpen('delete')" :title="$this->getModalTitle('delete')" :description="$this->getModalDescription('delete')" confirmMethod="confirmDeletion"
        cancelMethod="closeModal('delete')" />
</div>
