<div class="min-h-screen flex flex-col">
    {{-- Sidebar --}}
    <livewire:navigation-sidebar :section="$section" :folder-id="$folderId" key="navigation-sidebar" />
    <div class="ml-16 flex-1">
        {{-- Content Dinamic --}}
        @if ($isLoading)
            <div class="flex items-center justify-center h-full min-h-[400px]">
                <x-loader class="w-10 h-10 animate-spin text-indigo-600" />
            </div>
        @elseif ($section === 'edit-checklist')
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
    {{-- Demo Account Modal --}}
    <x-modal type="info" :show="$showDemoModal" title="Информация" :description="'Добро пожаловать в Leaf Note! Вы используете демо-аккаунт с ограниченным сроком действия до ' .
        $demoExpirationTime .
        '. По истечении этого периода аккаунт и все связанные с ним данные будут автоматически удалены. Чтобы получить полный доступ к возможностям приложения, рекомендуем создать полноценный профиль.'" confirmMethod="closeDemoModal" />
</div>
