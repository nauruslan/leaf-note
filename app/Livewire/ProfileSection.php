<?php

namespace App\Livewire;

use App\Dto\ProfileDto;
use App\Dto\SafePasswordDto;
use App\Dto\TrashSettingsDto;
use App\Dto\UserStatisticsDto;
use App\Services\UserService;
use App\Services\SafePasswordService;
use App\Services\TrashService;
use App\Services\StatisticsService;
use App\Services\ProfileValidationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Computed;

class ProfileSection extends Component
{
    // Заголовки
    public string $heading = 'Настройки профиля';
    public string $subheading = 'Управление вашими личными данными и настройками';

    // Личные данные с декларативной валидацией
    #[Rule('required|string|max:255')]
    public string $name = '';

    #[Rule('required|email|max:255')]
    public string $email = '';

    // Настройки
    public string $notificationsEnabled = '0';
    public string $autoDeleteDays = 'disabled';

    // Смена пароля
    public string $currentPassword = '';
    public string $newPassword = '';
    public string $confirmPassword = '';

    // Сейф пароль
    public string $safeCurrentPassword = '';
    public string $safePassword = '';
    public string $safeConfirmPassword = '';

    // Состояние
    public bool $hasUnsavedChanges = false;

    // Модальные окна
    public bool $showPasswordResetModal = false;
    public bool $showSafePasswordResetModal = false;

    // Состояние отправки
    public bool $sendingSafePasswordReset = false;
    public bool $safePasswordResetSent = false;

    // Защищённые от параллельных запросов
    #[Locked]
    public bool $hasSafePassword = false;

    #[Locked]
    public bool $canChangePassword = true;

    // Исходные значения для сравнения
    protected string $originalName = '';
    protected string $originalEmail = '';
    protected string $originalNotificationsEnabled = '0';
    protected string $originalAutoDeleteDays = 'disabled';

    // Внедряемые сервисы
    protected UserService $userService;
    protected SafePasswordService $safePasswordService;
    protected TrashService $trashService;
    protected StatisticsService $statisticsService;
    protected ProfileValidationService $validationService;

    public function boot(
        UserService $userService,
        SafePasswordService $safePasswordService,
        TrashService $trashService,
        StatisticsService $statisticsService,
        ProfileValidationService $validationService,
    ): void {
        $this->userService = $userService;
        $this->safePasswordService = $safePasswordService;
        $this->trashService = $trashService;
        $this->statisticsService = $statisticsService;
        $this->validationService = $validationService;
    }

    public function mount(): void
    {
        $user = Auth::user();

        // Личные данные
        $this->name = $user->name ?? '';
        $this->email = $user->email ?? '';

        // Настройки
        $this->notificationsEnabled = ($user->notifications_enabled ?? false) ? '1' : '0';
        $this->autoDeleteDays = $this->trashService->getAutoDeleteDays($user->id);

        // Статистика загружается через #[Computed]
        $this->hasSafePassword = $this->safePasswordService->hasPassword($user->id);
        $this->canChangePassword = $this->userService->canChangePassword($user->id);

        // Сохраняем исходные значения
        $this->initOriginalValues();
    }

    // Вычисляемое свойство для статистики
    #[Computed]
    public function statistics(): UserStatisticsDto
    {
        return $this->statisticsService->getUserStatistics(Auth::id());
    }

    // Универсальный обработчик изменений
    public function updated($property): void
    {
        // Отслеживаем изменения только для нужных свойств
        $trackedProperties = [
            'name', 'email', 'notificationsEnabled', 'autoDeleteDays',
            'currentPassword', 'newPassword', 'confirmPassword',
            'safeCurrentPassword', 'safePassword', 'safeConfirmPassword',
        ];

        if (in_array($property, $trackedProperties)) {
            $this->hasUnsavedChanges = $this->hasChanges();
        }
    }

    // Проверка наличия изменений
    protected function hasChanges(): bool
    {
        // Проверяем личные данные и настройки
        if ($this->originalName !== $this->name) {
            return true;
        }
        if ($this->originalEmail !== $this->email) {
            return true;
        }
        if ($this->originalNotificationsEnabled !== $this->notificationsEnabled) {
            return true;
        }
        if ($this->originalAutoDeleteDays !== $this->autoDeleteDays) {
            return true;
        }

        // Проверяем поля смены пароля аккаунта
        if (!empty($this->currentPassword) || !empty($this->newPassword) || !empty($this->confirmPassword)) {
            return true;
        }

        // Проверяем поля пароля сейфа
        if (!empty($this->safePassword) || !empty($this->safeConfirmPassword)) {
            return true;
        }
        if ($this->hasSafePassword && !empty($this->safeCurrentPassword)) {
            return true;
        }

        return false;
    }

    // Инициализация оригинальных значений
    protected function initOriginalValues(): void
    {
        $this->originalName = $this->name;
        $this->originalEmail = $this->email;
        $this->originalNotificationsEnabled = $this->notificationsEnabled;
        $this->originalAutoDeleteDays = $this->autoDeleteDays;
    }

    // Сохранить профиль
    public function saveProfile(): void
    {
        if (!$this->hasChanges()) {
            $this->dispatch('notification', [
                'title' => 'Информация',
                'content' => 'Нет изменений для сохранения',
                'type' => 'info'
            ]);
            return;
        }

        try {
            // Валидация личных данных
            $this->validationService->validateProfile([
                'name' => $this->name,
                'email' => $this->email,
            ]);

            // Обновляем профиль
            $user = $this->userService->updateProfile(
                Auth::id(),
                new ProfileDto(
                    name: $this->name,
                    email: $this->email,
                    notificationsEnabled: $this->notificationsEnabled === '1',
                )
            );

            // Отправляем событие для обновления JS переменной
            $this->dispatch('notifications-settings-updated', enabled: $user->notifications_enabled);

            // Сохраняем настройки корзины
            $this->trashService->updateAutoDeleteDays(
                Auth::id(),
                new TrashSettingsDto(autoDeleteDays: $this->autoDeleteDays)
            );

            // Смена пароля аккаунта
            if (!empty($this->currentPassword) || !empty($this->newPassword) || !empty($this->confirmPassword)) {
                $this->changePassword();
            }

            // Сохранение пароля сейфа
            if (!empty($this->safePassword) || !empty($this->safeConfirmPassword)) {
                $this->saveSafePassword();
            }

            // Очистка полей
            $this->clearPasswordFields();

            // Обновляем исходные значения
            $this->initOriginalValues();
            $this->hasUnsavedChanges = false;

            $this->dispatch('notification', [
                'title' => 'Успешно',
                'content' => 'Настройки сохранены',
                'type' => 'success'
            ]);

        } catch (ValidationException $e) {
            $this->dispatch('notification', [
                'title' => 'Внимание',
                'content' => 'Пожалуйста, исправьте ошибки в форме',
                'type' => 'warning'
            ]);
            throw $e;
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('notification', [
                'title' => 'Внимание',
                'content' => $e->getMessage(),
                'type' => 'warning'
            ]);
        }
    }

    // Сменить пароль аккаунта
    protected function changePassword(): void
    {
        if (!$this->canChangePassword) {
            return;
        }

        if (empty($this->currentPassword) && empty($this->newPassword) && empty($this->confirmPassword)) {
            return;
        }

        $this->validationService->validatePasswordChange([
            'currentPassword' => $this->currentPassword,
            'newPassword' => $this->newPassword,
            'confirmPassword' => $this->confirmPassword,
        ]);

        $this->userService->changePassword(
            Auth::id(),
            $this->currentPassword,
            $this->newPassword
        );
    }

    // Установить или изменить пароль сейфа
    protected function saveSafePassword(): void
    {
        // Если пароль уже установлен - нужен текущий пароль для изменения
        if ($this->hasSafePassword) {
            if (empty($this->safeCurrentPassword) && empty($this->safePassword) && empty($this->safeConfirmPassword)) {
                return;
            }
        } else {
            if (empty($this->safePassword) && empty($this->safeConfirmPassword)) {
                return;
            }
        }

        $this->validationService->validateSafePassword([
            'currentPassword' => $this->safeCurrentPassword,
            'password' => $this->safePassword,
            'confirmPassword' => $this->safeConfirmPassword,
        ], $this->hasSafePassword);

        $this->safePasswordService->setPassword(
            Auth::id(),
            new SafePasswordDto(
                currentPassword: $this->safeCurrentPassword ?: null,
                password: $this->safePassword,
                confirmPassword: $this->safeConfirmPassword,
            )
        );

        $this->hasSafePassword = true;
    }

    // Очистка полей паролей
    protected function clearPasswordFields(): void
    {
        $this->currentPassword = '';
        $this->newPassword = '';
        $this->confirmPassword = '';
        $this->safeCurrentPassword = '';
        $this->safePassword = '';
        $this->safeConfirmPassword = '';
    }

    // Отменить изменения
    public function cancel(): void
    {
        $this->mount();
        $this->dispatch('notification', [
            'title' => 'Информация',
            'content' => 'Изменения отменены',
            'type' => 'info'
        ]);
    }

    // Модальные окна
    public function openAccountPasswordResetModal(): void
    {
        $this->showPasswordResetModal = true;
    }

    public function closeAccountPasswordResetModal(): void
    {
        $this->showPasswordResetModal = false;
    }

    public function openSafePasswordResetModal(): void
    {
        $this->showSafePasswordResetModal = true;
    }

    public function closeSafePasswordResetModal(): void
    {
        $this->showSafePasswordResetModal = false;
    }

    // Сбросить состояние отправки ссылки
    public function dismissSafePasswordResetSent(): void
    {
        $this->safePasswordResetSent = false;
    }

    // Отправить ссылку для сброса пароля аккаунта
    public function sendAccountPasswordResetLink(): void
    {
        try {
            $success = $this->userService->sendPasswordResetLink($this->email);

            if (!$success) {
                $this->closeAccountPasswordResetModal();
                $this->dispatch('notification', [
                    'title' => 'Ошибка',
                    'content' => 'Не удалось отправить ссылку для сброса пароля',
                    'type' => 'danger'
                ]);
                return;
            }

            $this->closeAccountPasswordResetModal();
            $this->dispatch('notification', [
                'title' => 'Успешно',
                'content' => 'Ссылка для сброса пароля отправлена на вашу почту. Проверьте ваш email и перейдите по ссылке для установки нового пароля.',
                'type' => 'success'
            ]);
        } catch (\Exception $e) {
            Log::error('Account password reset error: ' . $e->getMessage());
            $this->closeAccountPasswordResetModal();
            $this->dispatch('notification', [
                'title' => 'Ошибка',
                'content' => 'Не удалось отправить ссылку для сброса пароля',
                'type' => 'danger'
            ]);
        }
    }

    // Отправить ссылку для сброса пароля сейфа
    public function sendSafePasswordResetLink(): void
    {
        $this->sendingSafePasswordReset = true;

        try {
            $this->safePasswordService->sendResetLink(Auth::id());

            $this->closeSafePasswordResetModal();
            $this->dispatch('notification', [
                'title' => 'Успешно',
                'content' => 'Ссылка для сброса пароля сейфа отправлена на вашу почту. Проверьте ваш email и перейдите по ссылке для подтверждения сброса.',
                'type' => 'success'
            ]);
        } catch (\InvalidArgumentException $e) {
            $this->closeSafePasswordResetModal();
            $this->dispatch('notification', [
                'title' => 'Ошибка',
                'content' => $e->getMessage(),
                'type' => 'danger'
            ]);
        } catch (\Exception $e) {
            Log::error('Safe password reset error: ' . $e->getMessage());
            $this->closeSafePasswordResetModal();
            $this->dispatch('notification', [
                'title' => 'Ошибка',
                'content' => 'Не удалось отправить ссылку для сброса пароля',
                'type' => 'danger'
            ]);
        } finally {
            $this->sendingSafePasswordReset = false;
        }
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.profile');
    }
}