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
        'black'              => ['label' => 'Чёрный',           'hex' => '#000000'],
        'white'              => ['label' => 'Белый',            'hex' => '#FFFFFF'],
        'neon-pink'          => ['label' => 'Неоновый розовый', 'hex' => '#FF2EC4'],
        'cyber-yellow'       => ['label' => 'Кибер-жёлтый',    'hex' => '#FFE500'],
        'aqua-pulse'         => ['label' => 'Аква-пульс',      'hex' => '#00F5D4'],
        'electric-blue'      => ['label' => 'Электро-синий',   'hex' => '#0096FF'],
        'hyper-violet'       => ['label' => 'Гипер-фиолет',    'hex' => '#9D00FF'],
        'lime-flash'         => ['label' => 'Лайм-вспышка',    'hex' => '#C6FF00'],
        'coral-pop'          => ['label' => 'Коралл-поп',      'hex' => '#FF6F61'],
        'mint-spark'         => ['label' => 'Мята-искра',      'hex' => '#4EFFC7'],
        'tangerine-burst'    => ['label' => 'Мандарин-взрыв',  'hex' => '#FF8A00'],
        'magenta-flare'      => ['label' => 'Маджента-вспышка','hex' => '#FF0F7B'],
        'sky-laser'          => ['label' => 'Небо-лазер',      'hex' => '#4CC9F0'],
        'acid-green'         => ['label' => 'Кислотный зелёный','hex' => '#7FFF00'],
        'bubblegum-bright'   => ['label' => 'Бабл-гам',       'hex' => '#FF77E9'],
        'ultraviolet-neon'   => ['label' => 'Ультрафиолет',    'hex' => '#B300FF'],
        'ocean-pulse'        => ['label' => 'Океан-пульс',      'hex' => '#00C2FF'],
        'raspberry-shock'    => ['label' => 'Малиновый шок',   'hex' => '#FF005C'],
        'sunrise-peach'      => ['label' => 'Рассвет-персик',  'hex' => '#FFB067'],
        'emerald-flash'      => ['label' => 'Изумруд-вспышка','hex' => '#00FF85'],
        'neon-amber'         => ['label' => 'Неоновый янтарь', 'hex' => '#FFB800'],
        'plasma-purple'      => ['label' => 'Плазма-пурпур',   'hex' => '#C724FF'],
        'arctic-mint'        => ['label' => 'Арктическая мята', 'hex' => '#7CFFEA'],
        'cherry-laser'       => ['label' => 'Вишня-лазер',     'hex' => '#FF1744'],
        'neon-sky'           => ['label' => 'Неоновое небо',   'hex' => '#00E0FF'],
        'cosmic-blue'       => ['label' => 'Космический синий','hex' => '#3A0FFF'],
        'citrus-pop'         => ['label' => 'Цитрус-поп',      'hex' => '#FFD000'],
        'neon-teal'          => ['label' => 'Неоновая бирюза', 'hex' => '#00FFC6'],
        'hot-sunset'         => ['label' => 'Жаркий закат',    'hex' => '#FF4D00'],
        'holo-pink'          => ['label' => 'Голо-розовый',    'hex' => '#FF4FF8'],
    ];

    public const ICONS = [
        'folder'         => ['label' => 'Папка',          'icon' => 'folder'],
        'folder-open'    => ['label' => 'Открытая папка', 'icon' => 'folder-open'],
        'archive'        => ['label' => 'Архив',          'icon' => 'archive'],
        'book'           => ['label' => 'Книга',          'icon' => 'book'],
        'bookmark'       => ['label' => 'Закладка',       'icon' => 'bookmark'],
        'briefcase'      => ['label' => 'Работа',         'icon' => 'briefcase'],
        'calendar'       => ['label' => 'Календарь',      'icon' => 'calendar'],
        'camera'         => ['label' => 'Фото',           'icon' => 'camera'],
        'clipboard'      => ['label' => 'Буфер обмена',   'icon' => 'clipboard'],
        'cloud'          => ['label' => 'Облако',         'icon' => 'cloud'],
        'code-2'         => ['label' => 'Код',            'icon' => 'code-2'],
        'coffee'         => ['label' => 'Кофе',           'icon' => 'coffee'],
        'database'       => ['label' => 'База данных',    'icon' => 'database'],
        'file'           => ['label' => 'Файл',           'icon' => 'file'],
        'flag'           => ['label' => 'Флаг',           'icon' => 'flag'],
        'gamepad-2'      => ['label' => 'Игры',           'icon' => 'gamepad-2'],
        'gift'           => ['label' => 'Подарок',        'icon' => 'gift'],
        'globe'          => ['label' => 'Мир',            'icon' => 'globe'],
        'graduation-cap' => ['label' => 'Обучение',       'icon' => 'graduation-cap'],
        'heart'          => ['label' => 'Сердце',         'icon' => 'heart'],
        'home'           => ['label' => 'Дом',            'icon' => 'home'],
        'lightbulb'      => ['label' => 'Идея',           'icon' => 'lightbulb'],
        'lock'           => ['label' => 'Замок',           'icon' => 'lock'],
        'music'          => ['label' => 'Музыка',          'icon' => 'music'],
        'palette'        => ['label' => 'Палитра',         'icon' => 'palette'],
        'plane'          => ['label' => 'Путешествие',     'icon' => 'plane'],
        'rocket'         => ['label' => 'Ракета',          'icon' => 'rocket'],
        'shield'         => ['label' => 'Щит',             'icon' => 'shield'],
        'sparkles'       => ['label' => 'Искры',           'icon' => 'sparkles'],
        'star'           => ['label' => 'Звезда',           'icon' => 'star'],
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