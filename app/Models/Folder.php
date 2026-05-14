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

    public const ICONS = [
        // Основные
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
        'lock'           => ['label' => 'Замок',          'icon' => 'lock'],
        'music'          => ['label' => 'Музыка',         'icon' => 'music'],
        'palette'        => ['label' => 'Палитра',        'icon' => 'palette'],
        'plane'          => ['label' => 'Путешествие',    'icon' => 'plane'],
        'rocket'         => ['label' => 'Ракета',         'icon' => 'rocket'],
        'shield'         => ['label' => 'Щит',            'icon' => 'shield'],
        'sparkles'       => ['label' => 'Искры',          'icon' => 'sparkles'],
        'star'           => ['label' => 'Звезда',         'icon' => 'star'],
        // Дополнительные иконки для заметок
        'bell'           => ['label' => 'Уведомление',    'icon' => 'bell'],
        'binoculars'     => ['label' => 'Наблюдение',     'icon' => 'binoculars'],
        'brain'          => ['label' => 'Мозг',           'icon' => 'brain'],
        'brush'          => ['label' => 'Кисть',          'icon' => 'brush'],
        'bug'            => ['label' => 'Баг',            'icon' => 'bug'],
        'cake'           => ['label' => 'Торт',           'icon' => 'cake'],
        'car'            => ['label' => 'Машина',         'icon' => 'car'],
        'cat'            => ['label' => 'Кот',            'icon' => 'cat'],
        'check-circle'   => ['label' => 'Галочка',        'icon' => 'check-circle'],
        'chef-hat'       => ['label' => 'Кулинария',      'icon' => 'chef-hat'],
        'compass'        => ['label' => 'Компас',         'icon' => 'compass'],
        'cpu'            => ['label' => 'Процессор',      'icon' => 'cpu'],
        'crown'          => ['label' => 'Корона',         'icon' => 'crown'],
        'diamond'        => ['label' => 'Алмаз',          'icon' => 'diamond'],
        'dumbbell'       => ['label' => 'Спорт',          'icon' => 'dumbbell'],
        'eye'            => ['label' => 'Глаз',           'icon' => 'eye'],
        'feather'        => ['label' => 'Перо',           'icon' => 'feather'],
        'film'           => ['label' => 'Кино',           'icon' => 'film'],
        'flame'          => ['label' => 'Огонь',          'icon' => 'flame'],
        // Расширенный набор иконок
        'apple'          => ['label' => 'Яблоко',         'icon' => 'apple'],
        'award'          => ['label' => 'Награда',        'icon' => 'award'],
        'baby'           => ['label' => 'Младенец',       'icon' => 'baby'],
        'badge-dollar-sign' => ['label' => 'Деньги',      'icon' => 'badge-dollar-sign'],
        'baggage-claim'  => ['label' => 'Багаж',          'icon' => 'baggage-claim'],
        'banana'         => ['label' => 'Банан',          'icon' => 'banana'],
        'banknote'       => ['label' => 'Банкнота',       'icon' => 'banknote'],
        'battery-charging' => ['label' => 'Зарядка',      'icon' => 'battery-charging'],
        'beaker'         => ['label' => 'Колба',          'icon' => 'beaker'],
        'bed'            => ['label' => 'Кровать',        'icon' => 'bed'],
        'beer'           => ['label' => 'Пиво',           'icon' => 'beer'],
        'bike'           => ['label' => 'Велосипед',      'icon' => 'bike'],
        'bitcoin'        => ['label' => 'Биткоин',        'icon' => 'bitcoin'],
        'bluetooth'      => ['label' => 'Bluetooth',      'icon' => 'bluetooth'],
        'bomb'           => ['label' => 'Бомба',          'icon' => 'bomb'],
        'bone'           => ['label' => 'Кость',          'icon' => 'bone'],
        'book-open'      => ['label' => 'Открытая книга', 'icon' => 'book-open'],
        'bot'            => ['label' => 'Бот',            'icon' => 'bot'],
        'box'            => ['label' => 'Коробка',        'icon' => 'box'],
        'brain-circuit'  => ['label' => 'ИИ',             'icon' => 'brain-circuit'],
        'briefcase-business' => ['label' => 'Бизнес',    'icon' => 'briefcase-business'],
        'brush-cleaning' => ['label' => 'Уборка',       'icon' => 'brush-cleaning'],
        'bug-off'        => ['label' => 'Фикс бага',      'icon' => 'bug-off'],
        'building'       => ['label' => 'Здание',         'icon' => 'building'],
        'bus'            => ['label' => 'Автобус',        'icon' => 'bus'],
        'cable'          => ['label' => 'Кабель',         'icon' => 'cable'],
        'calculator'     => ['label' => 'Калькулятор',    'icon' => 'calculator'],
        'camera-off'     => ['label' => 'Камера выкл',    'icon' => 'camera-off'],
        'candy'          => ['label' => 'Конфета',        'icon' => 'candy'],
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