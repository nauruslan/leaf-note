<?php

namespace App\Services;

use App\Dto\CreateChecklistDto;
use App\Dto\CreateNoteDto;
use App\Dto\LocationDto;
use App\Dto\UpdateChecklistDto;
use App\Dto\UpdateNoteDto;
use App\Events\ChecklistCreated;
use App\Events\ChecklistDeleted;
use App\Events\ChecklistUpdated;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;

/**
 * Сервис для работы с заметками
 */
class NoteService
{
    public function __construct(
        private ContentService $contentService,
        private LocationService $locationService,
    ) {}

    /**
     * Найти чеклист по ID
     */
    public function findChecklist(int $userId, int $noteId): ?Note
    {
        return Note::where('user_id', $userId)
            ->where('type', Note::TYPE_CHECKLIST)
            ->find($noteId);
    }

    /**
     * Найти заметку по ID
     */
    public function findNote(int $userId, int $noteId): ?Note
    {
        return Note::where('user_id', $userId)
            ->where('type', Note::TYPE_NOTE)
            ->find($noteId);
    }

    /**
     * Создать чеклист
     */
    public function createChecklist(CreateChecklistDto $dto): Note
    {
        $note = new Note();
        $note->title = $dto->title;
        $note->type = Note::TYPE_CHECKLIST;
        $note->content = $dto->content;
        $note->is_favorite = $dto->isFavorite;
        $note->user_id = $dto->userId;

        $this->locationService->updateNoteLocation($note, $dto->location);

        $note->save();

        // Отправляем событие о создании чеклиста
        event(new ChecklistCreated($note, $dto->userId));

        return $note;
    }

    /**
     * Создать заметку
     */
    public function createNote(CreateNoteDto $dto): Note
    {
        $note = new Note();
        $note->title = $dto->title;
        $note->type = Note::TYPE_NOTE;
        $note->content = $dto->content;
        $note->is_favorite = $dto->isFavorite;
        $note->user_id = $dto->userId;

        $this->locationService->updateNoteLocation($note, $dto->location);

        $note->save();

        return $note;
    }

    /**
     * Обновить чеклист
     */
    public function updateChecklist(UpdateChecklistDto $dto): Note
    {
        $note = $this->findChecklist($dto->userId, $dto->noteId);

        if (!$note) {
            throw new \InvalidArgumentException('Чеклист не найден');
        }

        // Сохраняем старые значения местоположения для проверки изменений
        $oldFolderId = $note->folder_id;
        $oldSafeId = $note->safe_id;
        $oldArchiveId = $note->archive_id;

        $note->title = $dto->title;
        $note->content = $dto->content;
        $note->is_favorite = $dto->isFavorite;

        $this->locationService->updateNoteLocation($note, $dto->location);

        $note->save();

        // Отправляем событие об обновлении чеклиста
        event(new ChecklistUpdated($note, $dto->userId));

        return $note;
    }

    /**
     * Обновить заметку
     */
    public function updateNote(UpdateNoteDto $dto): Note
    {
        $note = $this->findNote($dto->userId, $dto->noteId);

        if (!$note) {
            throw new \InvalidArgumentException('Заметка не найдена');
        }

        // Сохраняем старые значения местоположения для проверки изменений
        $oldFolderId = $note->folder_id;
        $oldSafeId = $note->safe_id;
        $oldArchiveId = $note->archive_id;

        $note->title = $dto->title;
        $note->content = $dto->content;
        $note->is_favorite = $dto->isFavorite;

        $this->locationService->updateNoteLocation($note, $dto->location);

        $note->save();

        return $note;
    }

    /**
     * Проверить существование заметки с указанным названием
     */
    public function isTitleExists(int $userId, string $title, ?int $excludeNoteId = null): bool
    {
        $query = Note::where('user_id', $userId)
            ->where('title', $title)
            ->whereNull('trash_id');

        if ($excludeNoteId) {
            $query->where('id', '!=', $excludeNoteId);
        }

        return $query->exists();
    }

    /**
     * Удалить чеклист (переместить в корзину)
     */
    public function deleteChecklist(int $userId, int $noteId): array
    {
        $checklist = $this->findChecklist($userId, $noteId);

        if (!$checklist) {
            return [
                'success' => false,
                'message' => 'Список не найден',
            ];
        }

        if ($checklist->is_favorite) {
            $checklist->toggleFavorite();
        }

        if (!$checklist->moveToTrash()) {
            return [
                'success' => false,
                'message' => 'Корзина переполнена. Очистите корзину перед удалением.',
            ];
        }

        // Отправляем событие об удалении чеклиста
        event(new ChecklistDeleted($checklist, $userId));

        return [
            'success' => true,
            'message' => "Список «{$checklist->title}» отправлен в корзину",
        ];
    }

    /**
     * Удалить заметку (переместить в корзину)
     */
    public function deleteNote(int $userId, int $noteId): array
    {
        $note = $this->findNote($userId, $noteId);

        if (!$note) {
            return [
                'success' => false,
                'message' => 'Заметка не найдена',
            ];
        }

        if ($note->is_favorite) {
            $note->toggleFavorite();
        }

        if (!$note->moveToTrash()) {
            return [
                'success' => false,
                'message' => 'Корзина переполнена. Очистите корзину перед удалением.',
            ];
        }

        return [
            'success' => true,
            'message' => "Заметка «{$note->title}» отправлена в корзину",
        ];
    }

    /**
     * Переключить статус избранного
     */
    public function toggleFavorite(int $userId, int $noteId, bool $isFavorite): void
    {
        $note = Note::where('user_id', $userId)->find($noteId);

        if ($note) {
            $note->is_favorite = $isFavorite;
            $note->save();
        }
    }
}
