<?php

namespace App\Services;

use App\Dto\TrashSettingsDto;
use App\Models\Trash;

/**
 * Сервис для управления настройками корзины
 */
class TrashService
{
    /**
     * Получить настройку автоудаления из корзины
     */
    public function getAutoDeleteDays(int $userId): string
    {
        $trash = Trash::where('user_id', $userId)->first();
        if (!$trash) {
            return 'disabled';
        }

        $days = $trash->auto_delete_days ?? null;
        return $days ? (string) $days : 'disabled';
    }

    /**
     * Обновить настройку автоудаления
     */
    public function updateAutoDeleteDays(int $userId, TrashSettingsDto $dto): void
    {
        $trash = Trash::where('user_id', $userId)->first();
        if (!$trash) {
            return;
        }

        if ($dto->autoDeleteDays === 'disabled') {
            $trash->auto_delete_days = null;
        } elseif ($dto->autoDeleteDays === '1min') {
            $trash->auto_delete_days = '1min';
        } else {
            $trash->auto_delete_days = (int) $dto->autoDeleteDays;
        }

        $trash->save();
    }
}