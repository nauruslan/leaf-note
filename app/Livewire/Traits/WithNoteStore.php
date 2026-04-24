<?php
namespace App\Livewire\Traits;

use App\Models\Folder;
use App\Models\Safe;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

/**
 * Трейт для подгрузки списка папок и сейфа
 *
 * @property int|null $folderId
 * @property int|null $safeId
 */
trait WithNoteStore
{
    #[Computed]
    public function folders(): EloquentCollection
    {
        return Folder::forUser(Auth::user())
            ->active()
            ->orderBy('title')
            ->get();
    }

    #[Computed]
    public function safes(): Collection
    {
        return Safe::where('user_id', Auth::id())
            ->get()
            ->map(fn (Safe $safe): array => [
                'value' => 'safe_' . $safe->id,
                'text'  => $safe->name ?? "Сейф",
            ]);
    }

    #[Computed]
    public function archives(): Collection
    {
        return \App\Models\Archive::where('user_id', Auth::id())
            ->get()
            ->map(fn (\App\Models\Archive $archive): array => [
                'value' => 'archive_' . $archive->id,
                'text'  => $archive->name ?? "Архив",
            ]);
    }
}