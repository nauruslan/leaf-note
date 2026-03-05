<?php

namespace App\Models;

use App\Models\User;
use App\Models\Folder;
use App\Models\Trash;
use App\Models\Archive;
use App\Models\Safe;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class Note extends Model
{
    const TYPE_NOTE = 'note';
    const TYPE_CHECKLIST = 'checklist';

    protected $fillable = [
        'folder_id',
        'trash_id',
        'archive_id',
        'safe_id',
        'title',
        'type',
        'payload',
        'color',
        'is_favorite',
    ];

    protected $casts = [
        'payload' => 'array',
        'is_favorite' => 'boolean',
        'moved_to_trash_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // Инвалидация кэша при создании заметки в папке
        static::created(function (Note $note) {
            if ($note->folder_id) {
                $note->folder?->clearNotesCountCache();
            }
        });

        static::updating(function (Note $note) {
            // Перемещение В корзину: обнуляем ВСЁ кроме trash_id
            if (
                $note->isDirty('trash_id') &&
                $note->trash_id &&
                is_null($note->getOriginal('trash_id'))
            ) {
                $note->moved_to_trash_at = now();

                // Инвалидация кэша старой папки
                $oldFolderId = $note->getOriginal('folder_id');
                if ($oldFolderId) {
                    Cache::forget("folder.{$oldFolderId}.notes_count");
                }

                // Полная очистка других контейнеров и папки
                $note->folder_id = null;
                $note->archive_id = null;
                $note->safe_id = null;
            }

            // Восстановление ИЗ корзины: перемещаем в архив
            if (
                $note->isDirty('trash_id') &&
                is_null($note->trash_id) &&
                $note->getOriginal('trash_id')
            ) {
                $note->moved_to_trash_at = null;

                // Перемещаем в архив пользователя
                $note->archive_id = $note->user->archive->id;
            }

            // Инвалидация при изменении folder_id
            if ($note->isDirty('folder_id')) {
                $oldFolderId = $note->getOriginal('folder_id');
                if ($oldFolderId) {
                    Cache::forget("folder.{$oldFolderId}.notes_count");
                }
                if ($note->folder_id) {
                    Cache::forget("folder.{$note->folder_id}.notes_count");
                }
            }
        });

        // Инвалидация кэша при удалении заметки
        static::deleted(function (Note $note) {
            if ($note->folder_id) {
                $note->folder?->clearNotesCountCache();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function trash(): BelongsTo
    {
        return $this->belongsTo(Trash::class);
    }

    public function archive(): BelongsTo
    {
        return $this->belongsTo(Archive::class);
    }

    public function safe(): BelongsTo
    {
        return $this->belongsTo(Safe::class);
    }


    public function isNote(): bool
    {
        return $this->type === self::TYPE_NOTE;
    }

    public function isChecklist(): bool
    {
        return $this->type === self::TYPE_CHECKLIST;
    }

    public function isInTrash(): bool
    {
        return !is_null($this->trash_id);
    }

    public function isInArchive(): bool
    {
        return !is_null($this->archive_id);
    }

    public function isInSafe(): bool
    {
        return !is_null($this->safe_id);
    }

    public function isInFolder(): bool
    {
        return !is_null($this->folder_id);
    }

    public function isActive(): bool
    {
        return !$this->isInTrash();
    }

    public function isFavorite(): bool
    {
        return (bool) $this->is_favorite;
    }

    public function moveToTrash(): bool
    {
        $trash = $this->user->trash;

        if (!$trash->hasRoom()) {
            return false;
        }

        $this->update([
            'folder_id' => null,
            'archive_id' => null,
            'safe_id' => null,
            'trash_id' => $trash->id,
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
            'archive_id' => $this->user->archive->id,
        ]);

        $trash = $this->user->trash;
        $trash->decrementQuantity();
        $trash->save();

        return true;
    }

    public function moveToArchive(): bool
    {
        $this->update([
            'folder_id' => null,
            'trash_id' => null,
            'safe_id' => null,
            'archive_id' => $this->user->archive->id,
            'moved_to_trash_at' => null,
        ]);

        return true;
    }

    public function moveToSafe(): bool
    {
        $this->update([
            'folder_id' => null,
            'trash_id' => null,
            'archive_id' => null,
            'safe_id' => $this->user->safe->id,
            'moved_to_trash_at' => null,
        ]);

        return true;
    }

    public function moveToFolder(Folder $folder): bool
    {
        if ($this->isInTrash()) {
            return false;
        }

        $this->update([
            'folder_id' => $folder->id,
            'archive_id' => null,
            'safe_id' => null,
            'trash_id' => null,
            'moved_to_trash_at' => null,
        ]);

        return true;
    }

    public function toggleFavorite(): bool
    {
        $this->update([
            'is_favorite' => !$this->is_favorite,
        ]);

        return $this->is_favorite;
    }


    public function getTextAttribute(): ?string
    {
        if ($this->isNote()) {
            // Поддерживаем оба формата: строка и массив
            if (is_string($this->payload)) {
                return $this->payload;
            }
            return $this->payload['text'] ?? null;
        }
        return null;
    }


    public function setTextAttribute(string $text): void
    {
        $this->payload = ['text' => $text];
    }


    public function getChecklistItemsAttribute(): array
    {
        if (!$this->isChecklist()) {
            return [];
        }

        return $this->payload['items'] ?? [];
    }


    public function setChecklistItemsAttribute(array $items): void
    {
        $this->payload = ['items' => $items];
    }

    public function getCompletionPercentageAttribute(): int
    {
        if (!$this->isChecklist()) {
            return 0;
        }

        $items = $this->checklist_items;
        $total = count($items);
        if ($total === 0) {
            return 0;
        }

        $completed = collect($items)
            ->where('completed', true)
            ->count();

        return round(($completed / $total) * 100);
    }

    public function getLocationAttribute(): string
    {
        if ($this->isInTrash()) return 'trash';
        if ($this->isInArchive()) return 'archive';
        if ($this->isInSafe()) return 'safe';
        if ($this->isInFolder()) return 'folder';
        return 'root';
    }

    public function getIconAttribute(): string
    {
        return $this->isChecklist() ? 'checklist' : 'note';
    }
}
