<?php

namespace App\Models;

use App\Models\Archive;
use App\Models\Folder;
use App\Models\Safe;
use App\Models\Trash;
use App\Models\User;
use App\Services\ContentService;
use App\Services\ImageService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'content',
        'search_content',
        'is_favorite',
    ];

    protected $casts = [
        'content' => 'json',
        'is_favorite' => 'boolean',
        'moved_to_trash_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // Автоматическое удаление изображений при физическом удалении заметки
        static::deleting(function (Note $note) {
            $imageService = app(ImageService::class);
            $imageService->deleteNoteImages($note);
        });

        // Автоматическое заполнение search_content из content при создании и обновлении
        static::saving(function (Note $note) {
            if ($note->isDirty('content')) {
                $note->search_content = $note->extractTextFromContent();
            }
        });

        static::updating(function (Note $note) {
            // Move to trash
            if (
                $note->isDirty('trash_id') &&
                $note->trash_id &&
                is_null($note->getOriginal('trash_id'))
            ) {
                $note->moved_to_trash_at = now();
                // Не обнуляем folder_id - он нужен для связи с папкой при удалении/восстановлении
                // folder_id будет очищен только если сама папка удалена
                $note->archive_id = null;
                $note->safe_id = null;
            }

            // Restore from trash
            if (
                $note->isDirty('trash_id') &&
                is_null($note->trash_id) &&
                $note->getOriginal('trash_id')
            ) {
                $note->moved_to_trash_at = null;
                $note->archive_id = $note->user->archive?->id;
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

    /**
     * Scope: Заметки конкретного пользователя.
     */
    public function scopeForUser($query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Заметки не в корзине.
     */
    public function scopeNotInTrash($query): Builder
    {
        return $query->whereNull('trash_id');
    }

    /**
     * Scope: Заметки в корзине.
     */
    public function scopeInTrash($query): Builder
    {
        return $query->whereNotNull('trash_id');
    }

    /**
     * Scope: Заметки не в архиве.
     */
    public function scopeNotArchived($query): Builder
    {
        return $query->whereNull('archive_id');
    }

    /**
     * Scope: Заметки в архиве.
     */
    public function scopeArchived($query): Builder
    {
        return $query->whereNotNull('archive_id');
    }

    /**
     * Scope: Заметки не в сейфе.
     */
    public function scopeNotInSafe($query): Builder
    {
        return $query->whereNull('safe_id');
    }

    /**
     * Scope: Заметки в сейфе.
     */
    public function scopeInSafe($query): Builder
    {
        return $query->whereNotNull('safe_id');
    }

    /**
     * Scope: Активные заметки (не в корзине, архиве, сейфе).
     */
    public function scopeActive($query): Builder
    {
        return $query->whereNull('trash_id')
            ->whereNull('archive_id')
            ->whereNull('safe_id');
    }

    /**
     * Scope: Избранные заметки.
     */
    public function scopeFavorite($query): Builder
    {
        return $query->where('is_favorite', true);
    }

    /**
     * Scope: Заметки определённого типа.
     */
    public function scopeOfType($query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Только заметки (не чеклисты).
     */
    public function scopeNotes($query): Builder
    {
        return $query->where('type', self::TYPE_NOTE);
    }

    /**
     * Scope: Только чеклисты.
     */
    public function scopeChecklists($query): Builder
    {
        return $query->where('type', self::TYPE_CHECKLIST);
    }

    /**
     * Scope: Заметки в папке.
     */
    public function scopeInFolder($query, ?int $folderId = null): Builder
    {
        if ($folderId === null) {
            return $query->whereNotNull('folder_id');
        }
        return $query->where('folder_id', $folderId);
    }

    /**
     * Scope: Заметки без папки.
     */
    public function scopeWithoutFolder($query): Builder
    {
        return $query->whereNull('folder_id');
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
            'is_favorite' => false,
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
            'folder_id' => null,
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

    public function getColorAttribute(): string
    {
        return $this->folder?->color ?? '#FFFFFF';
    }

    public function getPreviewAttribute(): string
    {
        if (empty($this->content)) {
            return '';
        }

        $text = app(ContentService::class)->extractTextFromContent($this->content);

        return \Illuminate\Support\Str::limit($text, 150);
    }

    /**
     * Получить текст из контента
     * @deprecated Используйте ContentService::extractTextFromContent()
     */
    public function extractTextFromContent(): string
    {
        return app(ContentService::class)->extractTextFromContent($this->content);
    }

    public function getColorHexAttribute(): string
    {
        // Теперь color уже содержит hex значение
        return $this->color ?? '#FFFFFF';
    }

    public function getIconColorClassAttribute(): string
    {
        return $this->color_hex;
    }

    public function getTypeIconAttribute(): string
    {
        return $this->isChecklist() ? 'list' : 'file-text';
    }

    /**
     * Получить прогресс чеклиста
     * @deprecated Используйте ChecklistService::getProgress()
     */
    public function getChecklistProgress(): array
    {
        $dto = app(\App\Services\ChecklistService::class)->getProgress($this);

        return [
            'completed' => $dto->completed,
            'total' => $dto->total,
            'percentage' => $dto->percentage,
            'color' => $dto->color,
        ];
    }

    /**
     * Получить пути к изображениям
     * @deprecated Используйте ImageService::extractImagePaths()
     */
    public function getImagePaths(): array
    {
        return app(ImageService::class)->extractImagePaths($this->content);
    }

    /**
     * Удалить изображения заметки
     * @deprecated Используйте ImageService::deleteNoteImages()
     */
    public function deleteImages(): void
    {
        app(ImageService::class)->deleteNoteImages($this);
    }
}