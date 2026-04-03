<?php

namespace App\Models;

use App\Models\Note;
use App\Models\Trash;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Folder extends Model
{

    public const COLORS = [
        'black'   => ['label' => 'Черный',   'bg' => 'bg-black',        'border' => 'border-gray-700', 'ring' => 'focus:ring-gray-700',  'hex' => '#000000'],
        'gray'    => ['label' => 'Серый',    'bg' => 'bg-gray-500',     'border' => 'border-gray-600', 'ring' => 'focus:ring-gray-600',  'hex' => '#6b7280'],
        'red'     => ['label' => 'Красный',  'bg' => 'bg-red-500',      'border' => 'border-red-600',  'ring' => 'focus:ring-red-600',   'hex' => '#ef4444'],
        'orange'  => ['label' => 'Оранжевый','bg' => 'bg-orange-500',   'border' => 'border-orange-600','ring' => 'focus:ring-orange-600','hex' => '#f97316'],
        'yellow'  => ['label' => 'Желтый',   'bg' => 'bg-yellow-500',   'border' => 'border-yellow-600','ring' => 'focus:ring-yellow-600','hex' => '#eab308'],
        'green'   => ['label' => 'Зеленый',  'bg' => 'bg-green-500',    'border' => 'border-green-600', 'ring' => 'focus:ring-green-600', 'hex' => '#22c55e'],
        'blue'    => ['label' => 'Синий',    'bg' => 'bg-blue-500',     'border' => 'border-blue-600',  'ring' => 'focus:ring-blue-600',  'hex' => '#3b82f6'],
        'indigo'  => ['label' => 'Индиго',   'bg' => 'bg-indigo-500',   'border' => 'border-indigo-600', 'ring' => 'focus:ring-indigo-600','hex' => '#6366f1'],
        'purple'  => ['label' => 'Фиолетовый','bg' => 'bg-purple-500',  'border' => 'border-purple-600', 'ring' => 'focus:ring-purple-600','hex' => '#8b5cf6'],
        'pink'    => ['label' => 'Розовый',  'bg' => 'bg-pink-500',     'border' => 'border-pink-600',  'ring' => 'focus:ring-pink-600',  'hex' => '#ec4899'],
        'white'   => ['label' => 'Белый',    'bg' => 'bg-white',        'border' => 'border-gray-300', 'ring' => 'focus:ring-gray-400',  'hex' => '#ffffff'],
    ];

    public const ICONS = [
        'folder'      => ['label' => 'Папка',         'icon' => 'folder'],
        'folder-open' => ['label' => 'Открытая папка','icon' => 'folder-open'],
        'archive'     => ['label' => 'Архив',         'icon' => 'archive'],
        'book'        => ['label' => 'Книга',         'icon' => 'book'],
        'calendar'    => ['label' => 'Календарь',     'icon' => 'calendar'],
        'clipboard'   => ['label' => 'Буфер обмена',  'icon' => 'clipboard'],
        'cloud'       => ['label' => 'Облако',        'icon' => 'cloud'],
        'database'    => ['label' => 'База данных',   'icon' => 'database'],
        'file'        => ['label' => 'Файл',          'icon' => 'file'],
        'heart'       => ['label' => 'Сердце',        'icon' => 'heart'],
        'home'        => ['label' => 'Дом',           'icon' => 'home'],
        'star'        => ['label' => 'Звезда',        'icon' => 'star'],
    ];


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
            // Move to trash
            if ($folder->isDirty('trash_id') && $folder->trash_id && is_null($folder->getOriginal('trash_id'))) {
                $folder->moved_to_trash_at = now();
            }

            // Restore from trash
            if ($folder->isDirty('trash_id') && is_null($folder->trash_id) && $folder->getOriginal('trash_id')) {
                $folder->moved_to_trash_at = null;
            }
        });

        static::deleting(function (Folder $folder) {
            Note::where('folder_id', $folder->id)
                ->whereNull('trash_id')
                ->whereNull('archive_id')
                ->whereNull('safe_id')
                ->delete();
        });
    }

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

        // Получаем количество заметок для подсчёта
        $notesCount = $this->notes()
            ->whereNull('trash_id')
            ->whereNull('archive_id')
            ->whereNull('safe_id')
            ->count();

        // Проверяем, есть ли место с учётом заметок
        if (!$trash->hasRoom($notesCount + 1)) {
            return false;
        }

        // Перемещаем все активные заметки папки в корзину
        $this->notes()
            ->whereNull('trash_id')
            ->whereNull('archive_id')
            ->whereNull('safe_id')
            ->update([
                'trash_id' => $trash->id,
                // Не обнуляем folder_id - он нужен для связи с папкой
                'moved_to_trash_at' => now(),
            ]);

        // Увеличиваем счётчик корзины на количество заметок
        $trash->incrementQuantity($notesCount + 1);
        $trash->save();

        // Помещаем саму папку в корзину
        $this->update([
            'trash_id' => $trash->id,
            'moved_to_trash_at' => now(),
        ]);

        return true;
    }

    public function restoreFromTrash(): bool
    {
        if (!$this->isInTrash()) {
            return false;
        }

        $trash = $this->user->trash;

        // Восстанавливаем все заметки папки из корзины обратно в папку
        $notesCount = $this->notes()
            ->whereNotNull('trash_id')
            ->whereNull('archive_id')
            ->whereNull('safe_id')
            ->count();

        $this->notes()
            ->whereNotNull('trash_id')
            ->whereNull('archive_id')
            ->whereNull('safe_id')
            ->update([
                'trash_id' => null,
                'folder_id' => $this->id,
                'moved_to_trash_at' => null,
            ]);

        // Уменьшаем счётчик корзины на количество заметок
        $trash->decrementQuantity(1 + $notesCount);
        $trash->save();

        // Восстанавливаем саму папку
        $this->update([
            'trash_id' => null,
            'moved_to_trash_at' => null,
        ]);

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
