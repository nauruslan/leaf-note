<?php

namespace App\Models;

use App\Models\User;
use App\Models\Note;
use App\Models\Folder;
use App\Models\Archive;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;

class Safe extends Model
{
    protected $fillable = [
        'user_id',
        'password_hash',
        'max_attempts',
        'failed_attempts',
        'locked_until',
        'last_accessed_at',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected $casts = [
        'max_attempts' => 'integer',
        'failed_attempts' => 'integer',
        'locked_until' => 'datetime',
        'last_accessed_at' => 'datetime',
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class, 'safe_id');
    }


    /**
     * Заблокирован ли сейф?
     */
    public function isLocked(): bool
    {
        return $this->locked_until && now()->lt($this->locked_until);
    }

    /**
     * Доступен ли сейф для ввода пароля?
     */
    public function isAccessible(): bool
    {
        return !$this->isLocked();
    }

    /**
     * Достигнут ли лимит попыток?
     */
    public function hasReachedAttemptLimit(): bool
    {
        return $this->failed_attempts >= $this->max_attempts;
    }

    /**
     * Время до разблокировки в секундах.
     */
    public function getSecondsUntilUnlockAttribute(): int
    {
        if (!$this->isLocked()) {
            return 0;
        }
        return max(0, $this->locked_until->diffInSeconds(now()));
    }


    /**
     * Зафиксировать неудачную попытку.
     *
     * @return bool true если сейф заблокирован после этой попытки
     */
    public function recordFailedAttempt(): bool
    {
        $this->failed_attempts += 1;
        $this->save();

        if ($this->hasReachedAttemptLimit()) {
            $this->lockForMinutes(5);
            return true;
        }

        return false;
    }

    /**
     * Заблокировать сейф на указанное количество минут.
     */
    public function lockForMinutes(int $minutes = 15): void
    {
        $this->locked_until = now()->addMinutes($minutes);
        $this->save();
    }

    /**
     * Разблокировать сейф.
     */
    public function unlock(): void
    {
        $this->failed_attempts = 0;
        $this->locked_until = null;
        $this->save();
    }

    /**
     * Зафиксировать успешный доступ.
     */
    public function recordSuccessfulAccess(): void
    {
        $this->failed_attempts = 0;
        $this->locked_until = null;
        $this->last_accessed_at = now();
        $this->save();
    }


    /**
     * Количество заметок в сейфе.
     */
    public function getCountAttribute(): int
    {
        return $this->notes()->count();
    }

    /**
     * Переместить заметку из сейфа в папку.
     *
     * @throws \Exception если заметка не в этом сейфе
     */
    public function moveNoteToFolder(Note $note, Folder $folder): bool
    {
        if ($note->safe_id !== $this->id) {
            throw new \Exception('Заметка не находится в этом сейфе');
        }

        $note->update([
            'safe_id' => null,
            'folder_id' => $folder->id,
            'trash_id' => null,
            'archive_id' => null,
        ]);

        return true;
    }

    /**
     * Переместить заметку из сейфа в архив.
     *
     * @throws \Exception если заметка не в этом сейфе
     */
    public function moveNoteToArchive(Note $note, Archive $archive): bool
    {
        if ($note->safe_id !== $this->id) {
            throw new \Exception('Заметка не находится в этом сейфе');
        }

        $note->update([
            'safe_id' => null,
            'archive_id' => $archive->id,
            'folder_id' => null,
            'trash_id' => null,
        ]);

        return true;
    }

    /**
     * Удалить заметку из сейфа (в корзину).
     *
     * @throws \Exception если заметка не в этом сейфе
     */
    public function moveNoteToTrash(Note $note): bool
    {
        if ($note->safe_id !== $this->id) {
            throw new \Exception('Заметка не находится в этом сейфе');
        }

        return $note->moveToTrash();
    }


    /**
     * Установлен ли пароль?
     */
    public function hasPassword(): bool
    {
        return !empty($this->password_hash);
    }

    /**
     * Проверить пароль.
     */
    public function verifyPassword(string $password): bool
    {
        return Hash::check($password, $this->password_hash);
    }

    /**
     * Установить пароль.
     */
    public function setPassword(string $password): void
    {
        $this->password_hash = Hash::make($password);
        $this->save();
    }

    /**
     * Сбросить пароль.
     */
    public function resetPassword(): void
    {
        $this->password_hash = null;
        $this->save();
    }


    /**
     * Статус сейфа для интерфейса.
     */
    public function getStatusAttribute(): string
    {
        if ($this->isLocked()) {
            return 'locked';
        }
        if ($this->hasReachedAttemptLimit()) {
            return 'attempt_limit_reached';
        }
        return 'accessible';
    }

    /**
     * Пуст ли сейф?
     */
    public function isEmpty(): bool
    {
        return $this->count === 0;
    }
}
