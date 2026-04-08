<div class="min-h-screen flex flex-col">
    {{-- Sidebar --}}
    <livewire:navigation-sidebar :section="$section" :folder-id="$folderId" key="{{ $section }}-{{ $componentKey }}" />
    <div class="ml-16 flex-1">
        {{-- Content Dinamic --}}
        @if ($section === 'edit-checklist')
            <livewire:edit-checklist :note-id="$noteId" key="{{ $section }}-{{ $componentKey }}" />
        @elseif($section === 'edit-note')
            <livewire:edit-note :note-id="$noteId" key="{{ $section }}-{{ $componentKey }}" />
        @elseif ($section === 'edit-folder' && $folderId)
            <livewire:edit-folder :folder-id="$folderId" key="{{ $section }}-{{ $componentKey }}" />
        @else
            <livewire:is :component="$section . '-view'" :section="$section" :folder-id="$folderId"
                key="{{ $section }}-{{ $componentKey }}" />
        @endif
    </div>
    <div class="ml-16">
        {{-- Footer --}}
        <livewire:footer key="footer-{{ $componentKey }}" />
    </div>
</div>
