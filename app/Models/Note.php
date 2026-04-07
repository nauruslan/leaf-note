<?php

namespace App\Models;

use App\Models\Archive;
use App\Models\Folder;
use App\Models\Safe;
use App\Models\Trash;
use App\Models\User;
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
        'payload',
        'is_favorite',
    ];

    protected $casts = [
        'payload' => 'array',
        'is_favorite' => 'boolean',
        'moved_to_trash_at' => 'datetime',
    ];

    protected static function booted(): void
    {
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
        return $this->folder?->color ?? 'default';
    }

    public function getPreviewAttribute(): string
    {
        if (empty($this->payload)) {
            return '';
        }

        $text = $this->extractTextFromPayload();

        return \Illuminate\Support\Str::limit($text, 150);
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
        $blocks = [];

        foreach ($content as $node) {
            if (!is_array($node)) {
                continue;
            }

            $blockText = $this->extractTextFromNode($node);
            if ($blockText !== '') {
                $blocks[] = $blockText;
            }
        }

        return implode("\n", $blocks);
    }

    private function extractTextFromNode(array $node): string
    {
        $texts = [];

        if (isset($node['type']) && $node['type'] === 'text' && isset($node['text'])) {
            $texts[] = $node['text'];
        }

        if (isset($node['content']) && is_array($node['content'])) {
            $nestedText = $this->collectInlineTextFromContent($node['content']);
            if ($nestedText !== '') {
                $texts[] = $nestedText;
            }
        }

        return implode(' ', $texts);
    }

    private function collectInlineTextFromContent(array $content): string
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
                $texts[] = $this->collectInlineTextFromContent($node['content']);
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
            'red' => 'red-500',
            'orange' => 'orange-500',
            'yellow' => 'yellow-500',
            'green' => 'green-500',
            'blue' => 'blue-500',
            'indigo' => 'indigo-500',
            'purple' => 'purple-500',
            'pink' => 'pink-500',
            'gray' => 'gray-500',
            'black' => 'black',
            'white' => 'white',
            'default' => 'white',
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
