<div>
    <!-- Header Section -->
    <x-header :heading="$heading" :section="$section" />
    <!-- ControlPanel Section -->
    <x-note-editor-control-panel :folders="$this->folders" :safes="$this->safes" :archives="$this->archives" :dropdownValue="$dropdownValue"
        :folderId="$folderId" :safeId="$safeId" :archiveId="$archiveId" :is_favorite="$is_favorite" />
    <!-- Content Section -->
    <x-note-editor-content-section :title="$title" :content="$content" :note="$this->note" editorId="create-note-editor"
        imageUploadInputId="create-note-image-upload-input" contentInputId="note-content-input" contentDebounce="500ms" />
</div>
