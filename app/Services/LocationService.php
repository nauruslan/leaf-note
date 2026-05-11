<?php

namespace App\Services;

use App\Dto\LocationDto;
use App\Models\Archive;
use App\Models\Folder;
use App\Models\Note;
use App\Models\Safe;
use Illuminate\Support\Collection;

/**
 * Сервис для работы с местоположением заметок
 */
class LocationService
{
    /**
     * Получить название места хранения заметки
     */
    public function getLocationName(Note $note): string
    {
        if ($note->folder_id !== null) {
            $folder = Folder::find($note->folder_id);
            return $folder?->title ?? 'Папка';
        }

        if ($note->safe_id !== null) {
            $safe = Safe::find($note->safe_id);
            return $safe?->name ?? 'Сейф';
        }

        if ($note->archive_id !== null) {
            $archive = Archive::find($note->archive_id);
            return $archive?->name ?? 'Архив';
        }

        return 'Архив';
    }

    /**
     * Получить тип местоположения заметки
     */
    public function getLocationType(Note $note): string
    {
        if ($note->folder_id !== null) {
            return 'folder';
        }
        if ($note->safe_id !== null) {
            return 'safe';
        }
        if ($note->archive_id !== null) {
            return 'archive';
        }
        return 'root';
    }

    /**
     * Построить dropdown значение из ID местоположения
     */
    public function buildDropdownValue(?int $folderId, ?int $safeId, ?int $archiveId): ?string
    {
        if ($safeId !== null) {
            return 'safe_' . $safeId;
        }
        if ($archiveId !== null) {
            return 'archive_' . $archiveId;
        }
        if ($folderId !== null) {
            return (string) $folderId;
        }
        return null;
    }

    /**
     * Обновить местоположение заметки
     */
    public function updateNoteLocation(Note $note, LocationDto $location): void
    {
        if ($location->folderId !== null) {
            $note->folder_id = $location->folderId;
            $note->safe_id = null;
            $note->archive_id = null;
        } elseif ($location->safeId !== null) {
            $note->safe_id = $location->safeId;
            $note->folder_id = null;
            $note->archive_id = null;
        } elseif ($location->archiveId !== null) {
            $note->archive_id = $location->archiveId;
            $note->folder_id = null;
            $note->safe_id = null;
        } else {
            $note->folder_id = null;
            $note->safe_id = null;
            $note->archive_id = null;
        }
    }

    /**
     * Получить список сейфов для dropdown
     */
    public function getSafesForDropdown(int $userId): Collection
    {
        return Safe::where('user_id', $userId)
            ->get()
            ->map(fn (Safe $safe): array => [
                'value' => 'safe_' . $safe->id,
                'text' => $safe->name ?? 'Сейф',
            ]);
    }

    /**
     * Получить список архивов для dropdown
     */
    public function getArchivesForDropdown(int $userId): Collection
    {
        return Archive::where('user_id', $userId)
            ->get()
            ->map(fn (Archive $archive): array => [
                'value' => 'archive_' . $archive->id,
                'text' => $archive->name ?? 'Архив',
            ]);
    }

    /**
     * Проверить, изменилось ли местоположение
     */
    public function locationChanged(
        LocationDto $current,
        ?int $originalFolderId,
        ?int $originalSafeId,
        ?int $originalArchiveId,
    ): bool {
        return ($current->folderId !== $originalFolderId) ||
               ($current->safeId !== $originalSafeId) ||
               ($current->archiveId !== $originalArchiveId);
    }
}