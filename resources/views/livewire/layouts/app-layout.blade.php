<div class="min-h-screen flex flex-col">
    {{-- Sidebar --}}
    <livewire:navigation-sidebar :section="$section" :folder-id="$folderId" key="{{ $section }}-{{ $componentKey }}" />
    <div class="ml-16 flex-1">
        {{-- Content Dinamic --}}
        <livewire:is :component="$section . '-view'" :section="$section" :folder-id="$folderId" :search="$search"
            key="{{ $section }}-{{ $componentKey }}" />
    </div>
    <div class="ml-16">
        {{-- Footer --}}
        <livewire:footer key="footer-{{ $componentKey }}" />
    </div>
</div>
