<?php

namespace App\Models;

use App\Models\User;
use App\Models\Note;
use App\Models\Folder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trash extends Model
{

    protected $fillable = [
        'capacity',
        'current_quantity',
    ];


    protected $casts = [
        'capacity' => 'integer',
        'current_quantity' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | ОТНОШЕНИЯ
    |--------------------------------------------------------------------------
    */

    /**
     * Корзина принадлежит одному пользователю.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Заметки в корзине.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    /**
     * Папки в корзине.
     */
    public function folders(): HasMany
    {
        return $this->hasMany(Folder::class);
    }

    /*
    |--------------------------------------------------------------------------
    | БИЗНЕС-ЛОГИКА
    |--------------------------------------------------------------------------
    */

    /**
     * Проверка: заполнена ли корзина?
     */
    public function isFull(): bool
    {
        return $this->current_quantity >= $this->capacity;
    }

    /**
     * Есть ли место для новых элементов?
     *
     * @param int $count Количество элементов для добавления
     * @return bool
     */
    public function hasRoom(int $count = 1): bool
    {
        return ($this->current_quantity + $count) <= $this->capacity;
    }

    /**
     * Увеличить счётчик элементов.
     *
     * @return bool true при успехе, false если нет места
     */
    public function incrementQuantity(int $amount = 1): bool
    {
        if (!$this->hasRoom($amount)) {
            return false;
        }

        $this->current_quantity += $amount;
        return true;
    }

    /**
     * Уменьшить счётчик элементов.
     */
    public function decrementQuantity(int $amount = 1): void
    {
        $this->current_quantity = max(0, $this->current_quantity - $amount);
    }

    /**
     * Сбросить счётчик в 0.
     */
    public function resetQuantity(): void
    {
        $this->current_quantity = 0;
    }

    /**
     * Получить статистику корзины на основе реальных данных.
     *
     * @return array
     */
    public function getStats(): array
    {
        $notesCount = $this->notes()->count();
        $foldersCount = $this->folders()->count();
        $totalCount = $notesCount + $foldersCount;

        return [
            'notes_count' => $notesCount,
            'folders_count' => $foldersCount,
            'total_count' => $totalCount,
            'capacity' => $this->capacity,
            'remaining_space' => max(0, $this->capacity - $totalCount),
            'is_full' => $totalCount >= $this->capacity,
            'fill_percentage' => $this->capacity > 0
                ? min(100, round(($totalCount / $this->capacity) * 100))
                : 0,
        ];
    }
}