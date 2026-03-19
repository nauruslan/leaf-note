<div class="min-h-screen flex flex-col">
    {{-- Sidebar --}}
    <livewire:navigation-sidebar :section="$section" :folder-id="$folderId" key="{{ $section }}-{{ $componentKey }}" />
    <div class="ml-16 flex-1">
        {{-- Content Dinamic --}}
        @if ($section === 'edit-checklist' && $folderId)
            <livewire:edit-checklist :checklist-id="$folderId" :key="'edit-checklist-' . $folderId" />
        @elseif($section === 'edit-note' && $folderId)
            <livewire:edit-note :note-id="$folderId" :key="'edit-note-' . $folderId" />
        @elseif($section === 'edit-folder' && $folderId)
            <livewire:edit-folder :folder-id="$folderId" :key="'edit-folder-' . $folderId" />
        @else
            <livewire:is :component="$section . '-view'" :section="$section" :folder-id="$folderId" :search="$search"
                key="{{ $section }}-{{ $componentKey }}" />
        @endif
    </div>
    <div class="ml-16">
        {{-- Footer --}}
        <livewire:footer key="footer-{{ $componentKey }}" />
    </div>
</div>
