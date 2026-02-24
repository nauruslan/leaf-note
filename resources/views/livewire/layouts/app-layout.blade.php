<div class="flex">

    {{-- Sidebar --}}

    <livewire:navigation-sidebar :section="$section" :folder-id="$folderId"
        wire:key="navigation-{{ $section }}-{{ $componentKey }}" />

    <div class="ml-16 flex-1">

        {{-- Header --}}
        <livewire:is :component="'headers.header-' . $section" :section="$section" :folder-id="$folderId"
            wire:key="header-{{ $section }}-{{ $componentKey }}" />

        {{-- Control Panel --}}
        <livewire:is :component="'control-panels.control-panel-' . $section" :section="$section" :folder-id="$folderId"
            wire:key="control-panel-{{ $section }}-{{ $componentKey }}" />

        {{-- Content --}}
        <livewire:is :component="'content.content-' . $section" :section="$section" :folder-id="$folderId" :search="$search"
            wire:key="content-{{ $section }}-{{ $componentKey }}" />

        {{-- Pagination --}}
        <livewire:pagination wire:key="pagination-{{ $componentKey }}" />

        {{-- Footer --}}
        <livewire:footer wire:key="footer-{{ $componentKey }}" />

    </div>

</div>
