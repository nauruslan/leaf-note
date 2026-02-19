<?php

namespace App\Models;

use App\Models\User;
use App\Models\Trash;
use App\Models\Note;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Folder extends Model
{
    protected $fillable = [
        'title',
        'color',
        'icon',
        'trash_id',
    ];

    protected $casts = [
        'moved_to_trash_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function (Folder $folder) {
            // Перемещение В корзину
            if ($folder->isDirty('trash_id') && $folder->trash_id && is_null($folder->getOriginal('trash_id'))) {
                $folder->moved_to_trash_at = now();
            }

            // Восстановление ИЗ корзины
            if ($folder->isDirty('trash_id') && is_null($folder->trash_id) && $folder->getOriginal('trash_id')) {
                $folder->moved_to_trash_at = null;
            }
        });

        static::deleting(function (Folder $folder) {
            // Удаляем только обычные заметки (не защищённые контейнерами)
            Note::where('folder_id', $folder->id)
                ->whereNull('trash_id')
                ->whereNull('archive_id')
                ->whereNull('safe_id')
                ->delete();
        });
    }

    // ОТНОШЕНИЯ
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trash(): BelongsTo
    {
        return $this->belongsTo(Trash::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function activeNotes(): HasMany
    {
        return $this->hasMany(Note::class)
            ->whereNull('trash_id')
            ->whereNull('archive_id')
            ->whereNull('safe_id');
    }

    public function trashedNotes(): HasMany
    {
        return $this->hasMany(Note::class)
            ->whereNotNull('trash_id');
    }

    // ВСПОМОГАТЕЛЬНЫЕ МЕТОДЫ
    public function isInTrash(): bool
    {
        return !is_null($this->trash_id);
    }

    public function isActive(): bool
    {
        return is_null($this->trash_id);
    }

    public function moveToTrash(): bool
    {
        $trash = $this->user->trash;
        if (!$trash->hasRoom()) {
            return false;
        }

        $this->update([
            'trash_id' => $trash->id,
            'moved_to_trash_at' => now(),
        ]);

        $trash->incrementQuantity();
        $trash->save();

        return true;
    }

    public function restoreFromTrash(): bool
    {
        if (!$this->isInTrash()) {
            return false;
        }

        $this->update([
            'trash_id' => null,
            'moved_to_trash_at' => null,
        ]);

        $trash = $this->user->trash;
        $trash->decrementQuantity();
        $trash->save();

        return true;
    }

    public function getStats(): array
    {
        $active = $this->notes()->whereNull('trash_id')->count();
        $trashed = $this->notes()->whereNotNull('trash_id')->count();

        return [
            'active_notes_count' => $active,
            'trashed_notes_count' => $trashed,
            'total_notes_count' => $active + $trashed,
            'is_in_trash' => $this->isInTrash(),
            'color' => $this->color,
            'icon' => $this->icon,
        ];
    }

    public function canDisplayNotes(): bool
    {
        return $this->isActive();
    }

    // SCOPES
    public function scopeActive($query)
    {
        return $query->whereNull('trash_id');
    }

    public function scopeInTrash($query)
    {
        return $query->whereNotNull('trash_id');
    }

    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

}