<?php

namespace App\Services;

/**
 * Сервис для управления уведомлениями в приложении
 */
class NotificationService
{
    /**
     * Проверить и получить уведомление о сбросе пароля сейфа
     */
    public function checkSafePasswordResetNotification(): ?array
    {
        if (!session()->has('safe_password_reset')) {
            return null;
        }

        session()->forget('safe_password_reset');

        return [
            'title' => 'Внимание',
            'content' => 'Был сброшен пароль от сейфа',
            'type' => 'warning',
        ];
    }

    /**
     * Подготовить данные для уведомления
     */
    public function prepareNotificationData(string $title, string $content, string $type = 'info'): array
    {
        return [
            'title' => $title,
            'content' => $content,
            'type' => $type,
        ];
    }
}