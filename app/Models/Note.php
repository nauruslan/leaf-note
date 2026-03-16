<?php

namespace App\Models;

use App\Livewire\NavigationSidebar;
use App\Models\Archive;
use App\Models\Folder;
use App\Models\Safe;
use App\Models\Trash;
use App\Models\User;
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
            // Если создана сразу в "корне" (активная)
            if (!$note->isInTrash() && !$note->isInArchive() && !$note->isInSafe()) {
                NavigationSidebar::invalidateCountCache('dashboard');
                if ($note->isChecklist()) {
                    NavigationSidebar::invalidateCountCache('checklist');
                }
                if ($note->isFavorite()) {
                    NavigationSidebar::invalidateCountCache('favorite');
                }
            }
        });

        static::updating(function (Note $note) {
            // Перемещение В корзину
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

            // 1. Инвалидация кэша папки при смене
            if ($note->isDirty('folder_id')) {
                $old = $note->getOriginal('folder_id');
                if ($old) Cache::forget("folder.{$old}.notes_count");
                if ($note->folder_id) Cache::forget("folder.{$note->folder_id}.notes_count");
            }

            // 2. Логика перемещения в корзину
            if ($note->isDirty('trash_id') && $note->trash_id && is_null($note->getOriginal('trash_id'))) {
                // Заметка уходит из активных разделов
                NavigationSidebar::invalidateCountCache('dashboard');
                if ($note->isChecklist()) NavigationSidebar::invalidateCountCache('checklist');
                if ($note->isFavorite()) NavigationSidebar::invalidateCountCache('favorite');
                // Появляется в треше
                NavigationSidebar::invalidateCountCache('trash');
            }

            // 3. Логика восстановления из корзины
            if ($note->isDirty('trash_id') && is_null($note->trash_id) && $note->getOriginal('trash_id')) {
                NavigationSidebar::invalidateCountCache('trash');
                // Если восстанавливается в архив
                if ($note->archive_id) {
                    NavigationSidebar::invalidateCountCache('archive');
                }
            }

            // 4. Перемещение в АРХИВ (из активного состояния)
            if ($note->isDirty('archive_id') && $note->archive_id && is_null($note->getOriginal('archive_id'))) {
                 NavigationSidebar::invalidateCountCache('dashboard');
                 if ($note->isChecklist()) NavigationSidebar::invalidateCountCache('checklist');
                 if ($note->isFavorite()) NavigationSidebar::invalidateCountCache('favorite');
                 NavigationSidebar::invalidateCountCache('archive');
            }

            // 5. Перемещение в СЕЙФ
            if ($note->isDirty('safe_id') && $note->safe_id && is_null($note->getOriginal('safe_id'))) {
                 NavigationSidebar::invalidateCountCache('dashboard');
                 if ($note->isChecklist()) NavigationSidebar::invalidateCountCache('checklist');
                 if ($note->isFavorite()) NavigationSidebar::invalidateCountCache('favorite');
                 NavigationSidebar::invalidateCountCache('safe');
            }

            // 6. Изменение избранного
            if ($note->isDirty('is_favorite')) {
                // Валидно только для активных заметок
                if (!$note->isInTrash() && !$note->isInArchive() && !$note->isInSafe()) {
                    NavigationSidebar::invalidateCountCache('favorite');
                }
            }
        });

        // Инвалидация кэша при удалении заметки
        static::deleted(function (Note $note) {
            if ($note->folder_id) {
                $note->folder?->clearNotesCountCache();
            }
            NavigationSidebar::invalidateCountCache();
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

    public function getPreviewAttribute(): string
    {
        if (empty($this->payload)) {
            return '';
        }

        $text = $this->extractTextFromPayload();

        return \Illuminate\Support\Str::limit($text, 100);
    }

    private function extractTextFromPayload(): string
    {
        if (is_string($this->payload)) {
            $data = json_decode($this->payload, true);
        } else {
            $data = $this->payload;
        }

        if (!is_array($data) || !isset($data['content'])) {
            return '';
        }

        return $this->collectTextFromContent($data['content']);
    }


    private function collectTextFromContent(array $content): string
    {
        $texts = [];

        foreach ($content as $node) {
            if (!is_array($node)) {
                continue;
            }

            if (isset($node['type']) && $node['type'] === 'text' && isset($node['text'])) {
                $texts[] = $node['text'];
            }

            if (isset($node['content']) && is_array($node['content'])) {
                $texts[] = $this->collectTextFromContent($node['content']);
            }
        }

        return trim(implode(' ', $texts));
    }

    public function getColorClassAttribute(): string
    {
        $colorMap = [
            'black' => 'bg-gray-900',
            'gray' => 'bg-gray-500',
            'red' => 'bg-red-500',
            'orange' => 'bg-orange-500',
            'yellow' => 'bg-yellow-500',
            'green' => 'bg-green-500',
            'blue' => 'bg-blue-500',
            'indigo' => 'bg-indigo-500',
            'purple' => 'bg-purple-500',
            'pink' => 'bg-pink-500',
            'white' => 'bg-white',
            'default' => 'bg-white',
        ];

        return $colorMap[$this->color] ?? 'bg-white';
    }

    public function getIconColorClassAttribute(): string
    {
        return match($this->color) {
            'red' => 'fill-red-500',
            'orange' => 'fill-orange-500',
            'yellow' => 'fill-yellow-500',
            'green' => 'fill-green-500',
            'blue' => 'fill-blue-500',
            'indigo' => 'fill-indigo-500',
            'purple' => 'fill-purple-500',
            'pink' => 'fill-pink-500',
            'gray' => 'fill-gray-500',
            'black' => 'fill-gray-900',
            'white', 'default' => 'fill-black-500',
            default => 'fill-black-500',
        };
    }

    public function getTypeIconAttribute(): string
    {
        return $this->isChecklist() ? 'list' : 'file-text';
    }

    public function getProgressColor(int $percentage): string
    {
        if ($percentage <= 10) return '#FF4C4C';
        if ($percentage <= 30) return '#FF8A4C';
        if ($percentage <= 50) return '#FFC04C';
        if ($percentage <= 70) return '#B4D84C';
        if ($percentage <= 90) return '#6ED84C';
        return '#2ABF2A';
    }

    public function getChecklistProgress(): array
    {
        if (!$this->isChecklist() || empty($this->payload)) {
            return [
                'completed' => 0,
                'total' => 0,
                'percentage' => 0,
                'color' => '#FF4C4C',
            ];
        }

        $data = is_string($this->payload)
            ? json_decode($this->payload, true)
            : $this->payload;

        if (!is_array($data) || !isset($data['content'])) {
            return [
                'completed' => 0,
                'total' => 0,
                'percentage' => 0,
                'color' => '#FF4C4C',
            ];
        }

        $stats = $this->countChecklistItems($data['content']);

        $percentage = $stats['total'] > 0
            ? (int) round(($stats['completed'] / $stats['total']) * 100)
            : 0;

        $color = $this->getProgressColor($percentage);

        return [
            'completed' => $stats['completed'],
            'total' => $stats['total'],
            'percentage' => $percentage,
            'color' => $color,
        ];
    }

    private function countChecklistItems(array $content): array
    {
        $completed = 0;
        $total = 0;

        foreach ($content as $node) {
            if (!is_array($node)) {
                continue;
            }

            if (isset($node['type']) && $node['type'] === 'checklistItem') {
                $total++;
                if (isset($node['attrs']['checked']) && $node['attrs']['checked'] === true) {
                    $completed++;
                }
            }

            if (isset($node['content']) && is_array($node['content'])) {
                $nested = $this->countChecklistItems($node['content']);
                $completed += $nested['completed'];
                $total += $nested['total'];
            }
        }

        return ['completed' => $completed, 'total' => $total];
    }
}
