@props([
    'notes',
    'totalCount',
    'search',
    'emptyIcon' => 'layout-grid',
    'emptyTitle' => 'Заметок нет',
    'emptyDescription' => 'Создайте заметку, чтобы увидеть её здесь',
    'noResultsIcon' => 'search-x',
    'noResultsTitle' => 'Совпадений не найдено',
    'noResultsDescription' => 'Попробуйте изменить поисковый запрос',
    'noFilterResultsIcon' => 'layout-grid',
    'noFilterResultsTitle' => 'Совпадений нет',
    'noFilterResultsDescription' => 'Попробуйте изменить фильтры',
])

<div
    class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 mb-6 grid grid-cols-[repeat(auto-fill,minmax(320px,1fr))] gap-5">
    @forelse($notes as $note)
        <x-card :item="$note" :color="$note->color" />
    @empty
        <div class="col-span-full">
            @if ($totalCount === 0)
                <x-no-data :icon="$emptyIcon" :title="$emptyTitle" :description="$emptyDescription" />
            @elseif ($search)
                <x-no-data :icon="$noResultsIcon" :title="$noResultsTitle" :description="$noResultsDescription" />
            @else
                <x-no-data :icon="$noFilterResultsIcon" :title="$noFilterResultsTitle" :description="$noFilterResultsDescription" />
            @endif
        </div>
    @endforelse
</div>
@if ($notes->hasPages())
    <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 mb-6">
        {{ $notes->links('livewire.pagination') }}
    </div>
@endif
