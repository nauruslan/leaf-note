<div class="flex">

    {{-- Sidebar --}}

    <livewire:navigation-sidebar :section="$section" :folder-id="$folderId" key="{{ $section }}-{{ $componentKey }}" />


    <div class="ml-16 flex-1">

        {{-- Header --}}
        <livewire:is :component="'headers.header-' . $section" :section="$section" :folder-id="$folderId"
            key="header-{{ $section }}-{{ $componentKey }}" />

        {{-- Control Panel --}}
        <livewire:is :component="'control-panels.control-panel-' . $section" :section="$section" :folder-id="$folderId"
            key="control-panel-{{ $section }}-{{ $componentKey }}" />

        {{-- Content --}}
        <livewire:is :component="'content.content-' . $section" :section="$section" :folder-id="$folderId" :search="$search"
            key="content-{{ $section }}-{{ $componentKey }}" />


        {{-- Pagination --}}
        <livewire:pagination key="pagination-{{ $componentKey }}" />

        {{-- Footer --}}
        <livewire:footer key="footer-{{ $componentKey }}" />

    </div>

</div>
