@props([
    'folders' => [],
    'safes' => [],
    'archives' => [],
    'dropdownValue' => null,
    'folderId' => null,
    'safeId' => null,
    'archiveId' => null,
    'is_favorite' => false,
])

<div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="bg-white rounded-xl shadow-md p-5 border border-gray-100">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
            <!-- Actions Block: Now starts from the left -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Folder Selection -->
                <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Папка:</span>
                    <x-dropdown :options="$folders->map(fn($f) => ['value' => $f->id, 'text' => $f->title])->toArray()" :safes="$safes->toArray()" :archives="$archives->toArray()"
                        selected="{{ $dropdownValue ?? ($folderId ?? (($safeId ? 'safe_' . $safeId : null) ?? ($archiveId ? 'archive_' . $archiveId : null))) }}"
                        wireModel="dropdownValue" live width="150px" />
                </div>
                <!-- Favorite -->
                <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Избранное:</span>
                    <x-dropdown :options="[['value' => '1', 'text' => 'Да'], ['value' => '0', 'text' => 'Нет']]" selected="{{ $is_favorite ? '1' : '0' }}" wireModel="is_favorite" live
                        width="80px" data-dropdown-favorite />
                </div>
            </div>
        </div>
    </div>
</div>
