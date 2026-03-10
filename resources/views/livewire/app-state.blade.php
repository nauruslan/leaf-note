<div style="display:none">
    {{-- AppState --}}
</div>

@script
    <script>
        Livewire.on('stateUpdated', () => {
            window.scrollTo(0, 0);
        });
    </script>
@endscript
