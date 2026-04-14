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
        // Базовые
        'black'              => ['label' => 'Чёрный',           'hex' => '#000000'],
        'white'              => ['label' => 'Белый',            'hex' => '#FFFFFF'],
        // Неон + Digital-Glow
        'electric-lime'      => ['label' => 'Electric Lime',    'hex' => '#C6FF1A'],
        'cyber-mint'         => ['label' => 'Cyber Mint',       'hex' => '#7CFFCB'],
        'neon-aqua'          => ['label' => 'Neon Aqua',        'hex' => '#00F6FF'],
        'hyper-blue'         => ['label' => 'Hyper Blue',       'hex' => '#007BFF'],
        'laser-pink'         => ['label' => 'Laser Pink',       'hex' => '#FF2EC4'],
        'violet-pulse'       => ['label' => 'Violet Pulse',     'hex' => '#A600FF'],
        'toxic-yellow'       => ['label' => 'Toxic Yellow',     'hex' => '#F9FF00'],
        'plasma-orange'      => ['label' => 'Plasma Orange',    'hex' => '#FF7A00'],
        'infrared-red'       => ['label' => 'Infrared Red',     'hex' => '#FF0038'],
        'ultraviolet-beam'   => ['label' => 'Ultraviolet Beam', 'hex' => '#6D00FF'],
        // Y2K + Pop-Culture Brights
        'bubblegum-pop'      => ['label' => 'Bubblegum Pop',    'hex' => '#FF7AC8'],
        'candy-sky'          => ['label' => 'Candy Sky',        'hex' => '#7AD3FF'],
        'blue-raspberry'     => ['label' => 'Blue Raspberry',   'hex' => '#009DFF'],
        'grape-soda'         => ['label' => 'Grape Soda',       'hex' => '#B45CFF'],
        'tropical-punch'     => ['label' => 'Tropical Punch',   'hex' => '#FF4F70'],
        'sunset-peach'       => ['label' => 'Sunset Peach',     'hex' => '#FF9A7A'],
        'lemon-fizz'         => ['label' => 'Lemon Fizz',       'hex' => '#FFE84A'],
        'aqua-jelly'         => ['label' => 'Aqua Jelly',       'hex' => '#4CFFE3'],
        'pixel-pink'         => ['label' => 'Pixel Pink',       'hex' => '#FF4FE3'],
        'skyburst-blue'      => ['label' => 'Skyburst Blue',    'hex' => '#4A8CFF'],
        // Futuristic + Techno-Street
        'hologram-silver'    => ['label' => 'Hologram Silver',  'hex' => '#D7E2F2'],
        'chrome-pulse'       => ['label' => 'Chrome Pulse',     'hex' => '#A8B4C9'],
        'digital-teal'       => ['label' => 'Digital Teal',     'hex' => '#00C7A4'],
        'matrix-green'       => ['label' => 'Matrix Green',     'hex' => '#00FF7B'],
        'tech-magenta'       => ['label' => 'Tech Magenta',     'hex' => '#FF1B9D'],
        'neon-ember'         => ['label' => 'Neon Ember',       'hex' => '#FF5A2A'],
        'cyber-steel'        => ['label' => 'Cyber Steel',      'hex' => '#6F7A8A'],
        'glitch-purple'      => ['label' => 'Glitch Purple',    'hex' => '#7F00C9'],
        'electric-coral'     => ['label' => 'Electric Coral',   'hex' => '#FF5E7E'],
        'hyperwave-blue'     => ['label' => 'Hyperwave Blue',   'hex' => '#2A4CFF'],
        // Streetwear + Urban Energy
        'graffiti-lime'      => ['label' => 'Graffiti Lime',    'hex' => '#C8FF4F'],
        'spraycan-pink'      => ['label' => 'Spraycan Pink',    'hex' => '#FF3F9E'],
        'skatepark-blue'     => ['label' => 'Skatepark Blue',   'hex' => '#3FA9FF'],
        'urban-orange'       => ['label' => 'Urban Orange',     'hex' => '#FF6B2F'],
        'street-violet'      => ['label' => 'Street Violet',    'hex' => '#9D3FFF'],
        'concrete-mist'      => ['label' => 'Concrete Mist',    'hex' => '#C4C7CC'],
        'night-runner'       => ['label' => 'Night Runner',     'hex' => '#1A1F2B'],
        'metro-cyan'         => ['label' => 'Metro Cyan',       'hex' => '#00D4FF'],
        'tag-red'            => ['label' => 'Tag Red',          'hex' => '#FF2F45'],
        'fresh-mint'         => ['label' => 'Fresh Mint',       'hex' => '#A8FFD9'],
        // Bold Accents
        'shock-yellow'       => ['label' => 'Shock Yellow',     'hex' => '#FFF500'],
        'punch-red'          => ['label' => 'Punch Red',        'hex' => '#FF1A1A'],
        'vibe-blue'          => ['label' => 'Vibe Blue',        'hex' => '#0066FF'],
        'flash-purple'       => ['label' => 'Flash Purple',     'hex' => '#B900FF'],
        'heatwave-orange'    => ['label' => 'Heatwave Orange',  'hex' => '#FF8A00'],
        'aqua-flash'         => ['label' => 'Aqua Flash',       'hex' => '#00FFE1'],
        'pink-voltage'       => ['label' => 'Pink Voltage',     'hex' => '#FF00A8'],
        'lime-spark'         => ['label' => 'Lime Spark',       'hex' => '#D4FF00'],
        'ocean-neon'         => ['label' => 'Ocean Neon',       'hex' => '#00BFFF'],
        'ultra-magenta'      => ['label' => 'Ultra Magenta',    'hex' => '#FF00FF'],
        // Pastel Dreams
        'soft-lavender'      => ['label' => 'Soft Lavender',    'hex' => '#E6E6FA'],
        'blush-pink'         => ['label' => 'Blush Pink',       'hex' => '#FFB6C1'],
        'mint-cream'         => ['label' => 'Mint Cream',       'hex' => '#F5FFFA'],
        'peach-puff'         => ['label' => 'Peach Puff',       'hex' => '#FFDAB9'],
        'sky-blue'           => ['label' => 'Sky Blue',         'hex' => '#87CEEB'],
        'lemon-chiffon'      => ['label' => 'Lemon Chiffon',    'hex' => '#FFFACD'],
        'lavender-blush'     => ['label' => 'Lavender Blush',   'hex' => '#FFF0F5'],
        'misty-rose'         => ['label' => 'Misty Rose',       'hex' => '#FFE4E1'],
        'honeydew'           => ['label' => 'Honeydew',         'hex' => '#F0FFF0'],
        'azure'              => ['label' => 'Azure',            'hex' => '#F0FFFF'],
        // Earth Tones
        'terracotta'         => ['label' => 'Terracotta',       'hex' => '#E2725B'],
        'sage-green'         => ['label' => 'Sage Green',       'hex' => '#9DC183'],
        'sand-beige'         => ['label' => 'Sand Beige',       'hex' => '#F5F5DC'],
        'clay-brown'         => ['label' => 'Clay Brown',       'hex' => '#B66A50'],
        'forest-green'       => ['label' => 'Forest Green',     'hex' => '#228B22'],
        'slate-gray'         => ['label' => 'Slate Gray',       'hex' => '#708090'],
        'rust-orange'        => ['label' => 'Rust Orange',      'hex' => '#B7410E'],
        'olive-green'        => ['label' => 'Olive Green',      'hex' => '#808000'],
        'taupe'              => ['label' => 'Taupe',            'hex' => '#483C32'],
        'copper'             => ['label' => 'Copper',           'hex' => '#B87333'],
        // Ocean & Nature
        'coral-reef'         => ['label' => 'Coral Reef',       'hex' => '#FF7F50'],
        'seafoam'            => ['label' => 'Seafoam',          'hex' => '#2E8B57'],
        'deep-ocean'         => ['label' => 'Deep Ocean',       'hex' => '#006994'],
        'sunset-gold'        => ['label' => 'Sunset Gold',      'hex' => '#FFD700'],
        'aurora-purple'      => ['label' => 'Aurora Purple',    'hex' => '#9D00FF'],
        'tide-pool'          => ['label' => 'Tide Pool',        'hex' => '#008B8B'],
        'driftwood'          => ['label' => 'Driftwood',        'hex' => '#8B7355'],
        'seashell'           => ['label' => 'Seashell',         'hex' => '#FFF5EE'],
        'lagoon'             => ['label' => 'Lagoon',           'hex' => '#00CED1'],
        'kelp'               => ['label' => 'Kelp',             'hex' => '#556B2F'],
    ];

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
        'boat'           => ['label' => 'Лодка',          'icon' => 'boat'],
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
