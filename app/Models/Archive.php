<?php

namespace App\Models;

use App\Models\User;
use App\Models\Note;
use App\Models\Folder;
use App\Models\Safe;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Archive extends Model
{

    protected $fillable = [];

    /**
     * Архив принадлежит одному пользователю.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Заметки в архиве.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class, 'archive_id');
    }

    /**
     * Количество заметок в архиве.
     */
    public function getCountAttribute(): int
    {
        return $this->notes()->count();
    }

    /**
     * Переместить заметку из архива в папку.
     *
     * Объяснение:
     * - Проверяем, что заметка действительно в этом архиве
     * - Обнуляем archive_id
     * - Устанавливаем новую папку
     *
     * @param Note $note Заметка для перемещения
     * @param Folder $folder Целевая папка
     * @return bool true при успехе
     * @throws \Exception если заметка не в этом архиве
     */
    public function moveNoteToFolder(Note $note, Folder $folder): bool
    {
        // Проверяем, что заметка находится в этом архиве
        if ($note->archive_id !== $this->id) {
            throw new \Exception('Заметка не находится в этом архиве');
        }

        $note->update([
            'archive_id' => null,
            'folder_id' => $folder->id,
            'trash_id' => null,
            'safe_id' => null,
        ]);

        return true;
    }

    /**
     * Переместить заметку из архива в сейф.
     *
     * @param Note $note Заметка для перемещения
     * @param Safe $safe Целевой сейф
     * @return bool true при успехе
     * @throws \Exception если заметка не в этом архиве
     */
    public function moveNoteToSafe(Note $note, Safe $safe): bool
    {
        // Проверяем, что заметка находится в этом архиве
        if ($note->archive_id !== $this->id) {
            throw new \Exception('Заметка не находится в этом архиве');
        }

        $note->update([
            'archive_id' => null,
            'safe_id' => $safe->id,
            'folder_id' => null,
            'trash_id' => null,
        ]);

        return true;
    }

    /**
     * Удалить заметку из архива (переместить в корзину).
     *
     * @param Note $note Заметка для удаления
     * @return bool true при успехе
     * @throws \Exception если заметка не в этом архиве
     */
    public function moveNoteToTrash(Note $note): bool
    {
        // Проверяем, что заметка находится в этом архиве
        if ($note->archive_id !== $this->id) {
            throw new \Exception('Заметка не находится в этом архиве');
        }

        // Перемещаем в корзину пользователя
        return $note->moveToTrash();
    }

    /**
     * Переместить ВСЕ заметки из архива в указанную папку.
     *
     * Полезно для массового восстановления.
     *
     * @param Folder $folder Целевая папка
     * @return int Количество перемещённых заметок
     */
    public function moveAllNotesToFolder(Folder $folder): int
    {
        $count = 0;

        foreach ($this->notes as $note) {
            $this->moveNoteToFolder($note, $folder);
            $count++;
        }

        return $count;
    }


    public function isEmpty(): bool
    {
        return $this->count === 0;
    }


    public function hasNotes(): bool
    {
        return $this->count > 0;
    }
}
