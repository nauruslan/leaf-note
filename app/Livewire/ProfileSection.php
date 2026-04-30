<?php

namespace App\Livewire;

use App\Models\Note;
use App\Models\Folder;
use App\Models\Trash;
use App\Models\Safe;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class ProfileSection extends Component
{

    public $heading='Настройки профиля';
    public $subheading='Управление вашими личными данными и настройками';
    // Личные данные
    public string $name = '';
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

    // Статистика
    public int $notesCount = 0;
    public int $checklistsCount = 0;
    public int $foldersCount = 0;

    // Состояние
    public bool $hasSafePassword = false;
    public bool $canChangePassword = true;

    // Состояние отправки сброса пароля сейфа
    public bool $sendingSafePasswordReset = false;
    public bool $safePasswordResetSent = false;

    // Модальные окна для сброса паролей
    public bool $showPasswordResetModal = false;
    public bool $showSafePasswordResetModal = false;

    // Публичное свойство для отслеживания изменений
    public bool $hasUnsavedChanges = false;

    // Исходные значения для сравнения (публичные для сериализации Livewire)
    public string $originalName = '';
    public string $originalEmail = '';
    public string $originalNotificationsEnabled = '0';
    public string $originalAutoDeleteDays = 'disabled';

    public function mount(): void
    {
        $user = Auth::user();

        // Личные данные
        $this->name = $user->name ?? '';
        $this->email = $user->email ?? '';

        // Настройки
        $this->notificationsEnabled = ($user->notifications_enabled ?? false) ? '1' : '0';
        $this->autoDeleteDays = $this->getAutoDeleteDays($user);

        // Статистика
        $this->loadStatistics($user);

        // Safe пароль
        $safe = Safe::where('user_id', $user->id)->first();
        $this->hasSafePassword = $safe && $safe->hasPassword();

        // Проверка возможности смены пароля (только не демо)
        $this->canChangePassword = !$user->isDemoUser();

        // Сохраняем исходные значения для отслеживания изменений
        $this->initOriginalValues();
    }

    // Инициализация оригинальных значений
    private function initOriginalValues(): void
    {
        $this->originalName = $this->name;
        $this->originalEmail = $this->email;
        $this->originalNotificationsEnabled = $this->notificationsEnabled;
        $this->originalAutoDeleteDays = $this->autoDeleteDays;
    }

    // Отслеживаем изменения полей
    public function updatedName(): void
    {
        $this->hasUnsavedChanges = $this->hasChanges();
    }

    public function updatedEmail(): void
    {
        $this->hasUnsavedChanges = $this->hasChanges();
    }

    public function updatedNotificationsEnabled(): void
    {
        $this->hasUnsavedChanges = $this->hasChanges();
    }

    public function updatedAutoDeleteDays(): void
    {
        $this->hasUnsavedChanges = $this->hasChanges();
    }

    public function updatedCurrentPassword(): void
    {
        $this->hasUnsavedChanges = $this->hasChanges();
    }

    public function updatedNewPassword(): void
    {
        $this->hasUnsavedChanges = $this->hasChanges();
    }

    public function updatedConfirmPassword(): void
    {
        $this->hasUnsavedChanges = $this->hasChanges();
    }

    public function updatedSafeCurrentPassword(): void
    {
        $this->hasUnsavedChanges = $this->hasChanges();
    }

    public function updatedSafePassword(): void
    {
        $this->hasUnsavedChanges = $this->hasChanges();
    }

    public function updatedSafeConfirmPassword(): void
    {
        $this->hasUnsavedChanges = $this->hasChanges();
    }

    // Проверка наличия изменений
    public function hasChanges(): bool
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

    // Получить настройку автоудаления из корзины.
    private function getAutoDeleteDays($user): string
    {
        $trash = Trash::where('user_id', $user->id)->first();
        if (!$trash) {
            return 'disabled';
        }

        $days = $trash->auto_delete_days ?? null;
        return $days ? (string) $days : 'disabled';
    }

    // Загрузить статистику пользователя.
    private function loadStatistics($user): void
    {
        $this->notesCount = Note::where('user_id', $user->id)
            ->where('type', Note::TYPE_NOTE)
            ->whereNull('trash_id')
            ->whereNull('archive_id')
            ->whereNull('safe_id')
            ->count();

        $this->checklistsCount = Note::where('user_id', $user->id)
            ->where('type', Note::TYPE_CHECKLIST)
            ->whereNull('trash_id')
            ->whereNull('archive_id')
            ->whereNull('safe_id')
            ->count();

        $this->foldersCount = Folder::where('user_id', $user->id)
            ->whereNull('trash_id')
            ->count();
    }


    // Сохранить профиль
    public function saveProfile(): void
    {
        // Если нет изменений - не сохраняем
        if (!$this->hasChanges()) {
            $this->dispatch('notification', ['title' => 'Информация', 'content' => 'Нет изменений для сохранения', 'type' => 'info']);
            return;
        }

        // Сохраняем личные данные
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        $user = Auth::user();
        $user->name = $this->name;
        $user->email = $this->email;
        $user->notifications_enabled = $this->notificationsEnabled === '1';
        $user->save();

        // Отправляем событие для обновления JavaScript переменной
        $this->dispatch('notifications-settings-updated', enabled: $user->notifications_enabled);

        // Сохраняем настройки корзины
        $this->saveNotificationSettings($user);

        // Если заполнены поля для смены пароля - меняем пароль
        if (!empty($this->currentPassword) || !empty($this->newPassword) || !empty($this->confirmPassword)) {
            $this->changePassword();
        }

        // Если заполнены поля для пароля сейфа - сохраняем пароль сейфа
        if (!empty($this->safePassword) || !empty($this->safeConfirmPassword)) {
            $this->saveSafePassword();
        }

        $this->currentPassword = '';
        $this->newPassword = '';
        $this->confirmPassword = '';
        $this->safeCurrentPassword = '';
        $this->safePassword = '';
        $this->safeConfirmPassword = '';

        // Обновляем исходные значения после сохранения
        $this->initOriginalValues();
        $this->hasUnsavedChanges = false;
        $this->dispatch('notification', ['title' => 'Успешно', 'content' => 'Настройки сохранены', 'type' => 'success']);
    }

    // Сохранить настройки автоудаления.
    private function saveNotificationSettings($user): void
    {
        $trash = Trash::where('user_id', $user->id)->first();
        if ($trash) {
            if ($this->autoDeleteDays === 'disabled') {
                $autoDeleteDays = null;
            } elseif ($this->autoDeleteDays === '1min') {
                $autoDeleteDays = '1min';
            } else {
                $autoDeleteDays = (int) $this->autoDeleteDays;
            }
            $trash->auto_delete_days = $autoDeleteDays;
            $trash->save();
        }
    }

    // Сменить пароль аккаунта.
    private function changePassword(): void
    {
        // Если смена пароля недоступна - выходим
        if (!$this->canChangePassword) {
            return;
        }

        // Если все поля пусты - не меняем пароль
        if (empty($this->currentPassword) && empty($this->newPassword) && empty($this->confirmPassword)) {
            return;
        }

        $user = Auth::user();

        $this->validate([
            'currentPassword' => 'required|string',
            'newPassword' => 'required|string|min:8',
            'confirmPassword' => 'required|string|same:newPassword',
        ]);

        if (!Hash::check($this->currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'currentPassword' => ['Текущий пароль указан неверно'],
            ]);
        }

        $user->password = Hash::make($this->newPassword);
        $user->save();

        $this->currentPassword = '';
        $this->newPassword = '';
        $this->confirmPassword = '';
    }

    // Установить или изменить пароль сейфа.
    private function saveSafePassword(): void
    {
        // Если пароль уже установлен - нужен текущий пароль для изменения
        if ($this->hasSafePassword) {
            // Если поля пусты - ничего не делаем
            if (empty($this->safeCurrentPassword) && empty($this->safePassword) && empty($this->safeConfirmPassword)) {
                return;
            }

            $this->validate([
                'safeCurrentPassword' => 'required|string',
                'safePassword' => 'required|string|min:4',
                'safeConfirmPassword' => 'required|string|same:safePassword',
            ]);

            $user = Auth::user();
            $safe = Safe::where('user_id', $user->id)->first();

            if (!$safe->verifyPassword($this->safeCurrentPassword)) {
                throw ValidationException::withMessages([
                    'safeCurrentPassword' => ['Текущий пароль сейфа указан неверно'],
                ]);
            }
        } else {
            // Новый пароль - если поля пусты, ничего не делаем
            if (empty($this->safePassword) && empty($this->safeConfirmPassword)) {
                return;
            }

            $this->validate([
                'safePassword' => 'required|string|min:4',
                'safeConfirmPassword' => 'required|string|same:safePassword',
            ]);
        }

        $user = Auth::user();
        $safe = Safe::where('user_id', $user->id)->first();

        if (!$safe) {
            Safe::create([
                'user_id' => $user->id,
                'password_hash' => Hash::make($this->safePassword),
            ]);
        } else {
            $safe->setPassword($this->safePassword);
        }

        $this->safeCurrentPassword = '';
        $this->safePassword = '';
        $this->safeConfirmPassword = '';
        $this->hasSafePassword = true;
    }

    // Сбросить состояние отправки ссылки
    public function dismissSafePasswordResetSent(): void
    {
        $this->safePasswordResetSent = false;
    }

    //  Отменить изменения.
    public function cancel(): void
    {
        $this->mount();
        $this->dispatch('notification', ['title' => 'Информация', 'content' => 'Изменения отменены', 'type' => 'info']);
    }

    // Показать модальное окно подтверждения сброса пароля аккаунта
    public function openAccountPasswordResetModal(): void
    {
        $this->showPasswordResetModal = true;
    }

    // Скрыть модальное окно сброса пароля аккаунта
    public function closeAccountPasswordResetModal(): void
    {
        $this->showPasswordResetModal = false;
    }

    // Показать модальное окно подтверждения сброса пароля сейфа
    public function openSafePasswordResetModal(): void
    {
        $this->showSafePasswordResetModal = true;
    }

    // Скрыть модальное окно сброса пароля сейфа
    public function closeSafePasswordResetModal(): void
    {
        $this->showSafePasswordResetModal = false;
    }

    // Отправить ссылку для сброса пароля аккаунта
    public function sendAccountPasswordResetLink(): void
    {
        $user = Auth::user();

        try {
            $status = Password::sendResetLink($this->only('email'));

            if ($status != Password::RESET_LINK_SENT) {
                $this->closeAccountPasswordResetModal();
                $this->dispatch('notification', ['title' => 'Ошибка', 'content' => 'Не удалось отправить ссылку для сброса пароля', 'type' => 'danger']);
                return;
            }

            $this->closeAccountPasswordResetModal();
            $this->dispatch('notification', ['title' => 'Успешно', 'content' => 'Ссылка для сброса пароля отправлена на вашу почту. Проверьте ваш email и перейдите по ссылке для установки нового пароля.', 'type' => 'success']);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Account password reset error: ' . $e->getMessage());
            $this->closeAccountPasswordResetModal();
             $this->dispatch('notification', ['title' => 'Ошибка', 'content' => 'Не удалось отправить ссылку для сброса пароля', 'type' => 'danger']);
        }
    }

    // Отправить ссылку для сброса пароля сейфа (с модальным окном)
    public function sendSafePasswordResetLink(): void
    {
        $user = Auth::user();
        $safe = Safe::where('user_id', $user->id)->first();

        if (!$safe || !$safe->hasPassword()) {
            $this->closeSafePasswordResetModal();
            $this->dispatch('notification', ['title' => 'Ошибка', 'content' => 'Пароль сейфа не установлен', 'type' => 'danger']);
            return;
        }

        $this->sendingSafePasswordReset = true;

        try {
            // Шифруем ID сейфа
            $safeId = \Illuminate\Support\Facades\Crypt::encryptString($safe->id);

            // Генерируем подписанный URL на 60 минут
            $resetUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                'safe-password.reset',
                now()->addMinutes(60),
                ['safe_id' => $safeId]
            );

            // Отправляем email
            \Illuminate\Support\Facades\Mail::to($user->email)->send(
                new \App\Mail\SafePasswordResetMail($resetUrl, $user->name)
            );

            $this->closeSafePasswordResetModal();
            $this->dispatch('notification', ['title' => 'Успешно', 'content' => 'Ссылка для сброса пароля сейфа отправлена на вашу почту. Проверьте ваш email и перейдите по ссылке для подтверждения сброса.', 'type' => 'success']);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Safe password reset error: ' . $e->getMessage());
            $this->closeSafePasswordResetModal();
             $this->dispatch('notification', ['title' => 'Ошибка', 'content' => 'Не удалось отправить ссылку для сброса пароля', 'type' => 'danger']);
        } finally {
            $this->sendingSafePasswordReset = false;
        }
    }

public function render()
{
    return view('livewire.profile');
}
}
