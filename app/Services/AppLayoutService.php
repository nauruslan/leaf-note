<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * Сервис для управления логикой AppLayout компонента
 */
class AppLayoutService
{
    /**
     * Инициализировать состояние демо-модального окна
     */
    public function initializeDemoModal(User $user): array
    {
        if (!$user->isDemoUser()) {
            return [
                'showDemoModal' => false,
                'demoExpirationTime' => '',
            ];
        }

        $demoUserService = app(DemoUserService::class);

        return [
            'showDemoModal' => $demoUserService->shouldShowDemoModal($user),
            'demoExpirationTime' => $demoUserService->getDemoExpirationTime($user),
        ];
    }

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
     * Валидировать параметры навигации
     */
    public function validateNavigationParams(string $section, ?int $folderId = null, ?int $noteId = null): array
    {
        $validator = Validator::make([
            'section' => $section,
            'folderId' => $folderId,
            'noteId' => $noteId,
        ], [
            'section' => 'required|string|max:255',
            'folderId' => 'nullable|integer|min:1',
            'noteId' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException('Invalid navigation parameters: ' . $validator->errors()->first());
        }

        return [
            'section' => $section,
            'folderId' => $folderId,
            'noteId' => $noteId,
        ];
    }

    /**
     * Подготовить данные для навигации
     */
    public function prepareNavigationData(string $section, ?int $folderId = null, ?int $noteId = null): array
    {
        // Валидируем параметры
        $this->validateNavigationParams($section, $folderId, $noteId);

        // Получаем текущее состояние
        $currentState = [
            'section' => StateManager::get('section', 'dashboard-section'),
            'folderId' => StateManager::get('folderId'),
            'noteId' => StateManager::get('noteId'),
        ];

        // Проверяем, это переход на страницу редактирования
        $isEditSection = in_array($section, ['edit-note', 'edit-checklist', 'edit-folder']);

        if ($isEditSection && $currentState['section'] !== $section) {
            StateManager::set('previous_section', $currentState['section']);
            StateManager::set('previous_folderId', $currentState['folderId']);
            StateManager::set('previous_noteId', $currentState['noteId']);
        }

        return [
            'section' => $section,
            'folderId' => $folderId,
            'noteId' => $noteId,
            'previousSection' => $currentState['section'],
            'previousFolderId' => $currentState['folderId'],
            'previousNoteId' => $currentState['noteId'],
        ];
    }

    /**
     * Подготовить данные для состояния загрузки
     */
    public function prepareLoadingData(string $section, ?int $folderId = null, ?int $noteId = null): array
    {
        $this->validateNavigationParams($section, $folderId, $noteId);

        return [
            'isLoading' => true,
            'loadingSection' => $section,
            'loadingNoteId' => $noteId,
        ];
    }

    /**
     * Сбросить состояние загрузки
     */
    public function resetLoadingData(): array
    {
        return [
            'isLoading' => false,
            'loadingSection' => null,
            'loadingNoteId' => null,
        ];
    }

    /**
     * Инициализировать состояние компонента при загрузке
     */
    public function initializeComponentState(): array
    {
        return [
            'section' => StateManager::get('section', 'dashboard-section'),
            'folderId' => StateManager::get('folderId'),
            'noteId' => StateManager::get('noteId'),
        ];
    }
}
