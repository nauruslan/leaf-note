<?php

namespace App\Models;

use App\Models\User;
use App\Models\Folder;
use App\Models\Trash;
use App\Models\Archive;
use App\Models\Safe;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Note extends Model
{
    // Константы типов заметок
    const TYPE_NOTE = 'note';
    const TYPE_CHECKLIST = 'checklist';

    /**
     * Массово присваиваемые атрибуты.
     *
     * ВАЖНО:
     * - user_id НЕ включён — заполняется автоматически через отношение
     * - moved_to_trash_at НЕ включён — управляется автоматически через события
     * - Нет полей original_* — их нет в миграции
     */
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

    /**
     * Приведение типов.
     */
    protected $casts = [
        'payload' => 'array',
        'is_favorite' => 'boolean',
        'moved_to_trash_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | BOOT-ЛОГИКА (события модели)
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::updating(function (Note $note) {
            // Перемещение В корзину: обнуляем ВСЁ кроме trash_id
            if (
                $note->isDirty('trash_id') &&
                $note->trash_id &&
                is_null($note->getOriginal('trash_id'))
            ) {
                $note->moved_to_trash_at = now();

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
        });
    }

    /*
    |--------------------------------------------------------------------------
    | ОТНОШЕНИЯ
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | ПРОВЕРКИ СОСТОЯНИЯ
    |--------------------------------------------------------------------------
    */

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
        // Активная = не в корзине (может быть в папке, архиве или сейфе)
        return !$this->isInTrash();
    }

    public function isFavorite(): bool
    {
        return (bool) $this->is_favorite;
    }

    /*
    |--------------------------------------------------------------------------
    | МЕТОДЫ ПЕРЕМЕЩЕНИЯ (точная логика под ваше приложение)
    |--------------------------------------------------------------------------
    */

    /**
     * Переместить заметку в корзину.
     *
     * Логика:
     * - Обнуляем ВСЁ: folder_id, archive_id, safe_id
     * - Устанавливаем только trash_id и moved_to_trash_at
     */
    public function moveToTrash(): bool
    {
        $trash = $this->user->trash;

        // Проверяем место в корзине
        if (!$trash->hasRoom()) {
            return false;
        }

        // Полная очистка + перемещение в корзину
        $this->update([
            'folder_id' => null,
            'archive_id' => null,
            'safe_id' => null,
            'trash_id' => $trash->id,
        ]);

        // Обновляем счётчик корзины
        $trash->incrementQuantity();
        $trash->save();

        return true;
    }

    /**
     * Восстановить заметку из корзины.
     *
     * Логика:
     * - Убираем из корзины (trash_id = null)
     * - Автоматически перемещаем в архив пользователя
     */
    public function restoreFromTrash(): bool
    {
        if (!$this->isInTrash()) {
            return false;
        }

        // Восстановление = перемещение в архив
        $this->update([
            'trash_id' => null,
            'archive_id' => $this->user->archive->id,
        ]);

        // Уменьшаем счётчик корзины
        $trash = $this->user->trash;
        $trash->decrementQuantity();
        $trash->save();

        return true;
    }

    /**
     * Переместить заметку в архив.
     *
     * Логика:
     * - Обнуляем ВСЁ кроме archive_id
     */
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

    /**
     * Переместить заметку в сейф.
     *
     * Логика:
     * - Обнуляем ВСЁ кроме safe_id
     */
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

    /**
     * Переместить заметку в папку.
     *
     * Логика:
     * - Работает ТОЛЬКО для активных заметок (не в корзине)
     * - Обнуляем другие контейнеры
     */
    public function moveToFolder(Folder $folder): bool
    {
        if ($this->isInTrash()) {
            return false; // Нельзя перемещать из корзины напрямую
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

    /**
     * Добавить/убрать из избранного.
     */
    public function toggleFavorite(): bool
    {
        $this->update([
            'is_favorite' => !$this->is_favorite,
        ]);

        return $this->is_favorite;
    }

    /*
    |--------------------------------------------------------------------------
    | РАБОТА С СОДЕРЖИМЫМ (payload)
    |--------------------------------------------------------------------------
    */

    /**
     * Получить текст заметки (для текстовых заметок).
     */
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

    /**
     * Установить текст заметки.
     */
    public function setTextAttribute(string $text): void
    {
        $this->payload = ['text' => $text];
    }

    /**
     * Получить задачи чек-листа.
     *
     * Формат: [
     *   ['text' => 'Задача 1', 'completed' => false],
     *   ['text' => 'Задача 2', 'completed' => true],
     * ]
     */
    public function getChecklistItemsAttribute(): array
    {
        if (!$this->isChecklist()) {
            return [];
        }

        return $this->payload['items'] ?? [];
    }

    /**
     * Установить задачи чек-листа.
     */
    public function setChecklistItemsAttribute(array $items): void
    {
        $this->payload = ['items' => $items];
    }

    /**
     * Получить процент выполнения чек-листа.
     */
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

    /*
    |--------------------------------------------------------------------------
    | ВСПОМОГАТЕЛЬНЫЕ МЕТОДЫ
    |--------------------------------------------------------------------------
    */

    /**
     * Текущее местоположение заметки.
     */
    public function getLocationAttribute(): string
    {
        if ($this->isInTrash()) return 'trash';
        if ($this->isInArchive()) return 'archive';
        if ($this->isInSafe()) return 'safe';
        if ($this->isInFolder()) return 'folder';
        return 'root'; // Ошибка, без хранилища
    }

    /**
     * Иконка для заметки.
     */
    public function getIconAttribute(): string
    {
        return $this->isChecklist() ? 'checklist' : 'note';
    }
}
