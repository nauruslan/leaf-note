@props([
    'trashedNotes',
    'trashedFolders',
    'totalCount',
    'search',
    'filter',
    'section',
    'emptyIcon' => 'trash',
    'emptyTitle' => 'Корзина пуста',
    'emptyDescription' => 'Удалённые файлы будут отображаться здесь',
    'noResultsIcon' => 'search-x',
    'noResultsTitle' => 'Совпадений не найдено',
    'noResultsDescription' => 'Попробуйте изменить поисковый запрос',
    'noFilterResultsIcon' => 'trash',
    'noFilterResultsTitle' => 'Совпадений нет',
    'noFilterResultsDescription' => 'Попробуйте изменить фильтры',
])

@php
    $isSearching = !empty($search);
    $showNotes = $isSearching || $filter === 'all' || $filter === 'notes' || $filter === 'checklists';
    $showFolders = $isSearching || $filter === 'all' || $filter === 'folders';
    $hasFolders = $showFolders && $trashedFolders->count() > 0;
    $hasNotes = $showNotes && $trashedNotes->count() > 0;
    $hasResults = $hasFolders || $hasNotes;
@endphp

@php
    $itemsToShow = collect();
    if ($showFolders) {
        $itemsToShow = $itemsToShow->merge(
            $trashedFolders->map(fn($folder) => (object) ['type' => 'folder', 'data' => $folder]),
        );
    }
    if ($showNotes) {
        $itemsToShow = $itemsToShow->merge(
            $trashedNotes->map(fn($note) => (object) ['type' => 'note', 'data' => $note]),
        );
    }
@endphp

<div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 mb-6 grid grid-cols-[repeat(auto-fill,minmax(320px,1fr))] gap-5">
    @forelse($itemsToShow as $item)
        @if ($item->type === 'folder')
            <x-card :item="$item->data" :section="$section" type="folder" />
        @else
            <x-card :item="$item->data" :color="$item->data->color" :section="$section" />
        @endif
    @empty
        <div class="col-span-full">
            @if ($totalCount === 0)
                <x-no-data :icon="$emptyIcon" :title="$emptyTitle" :description="$emptyDescription" />
            @elseif (!empty($search))
                <x-no-data :icon="$noResultsIcon" :title="$noResultsTitle" :description="$noResultsDescription" />
            @else
                <x-no-data :icon="$noFilterResultsIcon" :title="$noFilterResultsTitle" :description="$noFilterResultsDescription" />
            @endif
        </div>
    @endforelse
</div>
@if ($hasResults && $trashedNotes->hasPages())
    <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 mb-6">
        {{ $trashedNotes->links('livewire.pagination') }}
    </div>
@endif
