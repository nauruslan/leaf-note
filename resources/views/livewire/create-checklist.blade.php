<div>
    <!-- Header Section -->
    <x-header :heading="$heading" :section="$section" />
    <!-- ControlPanel Section -->
    <x-checklist-editor-control-panel :folders="$this->folders" :safes="$this->safes" :archives="$this->archives" :dropdownValue="$dropdownValue"
        :folderId="$folderId" :safeId="$safeId" :archiveId="$archiveId" :is_favorite="$is_favorite" />
    <!-- Content Section -->
    <x-checklist-editor-content-section :title="$title" :content="$content" :checklist="null"
        editorId="create-checklist-editor" contentInputId="checklist-content-input" contentDebounce="500ms" />
</div>
