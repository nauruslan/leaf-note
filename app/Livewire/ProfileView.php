<?php

namespace App\Livewire;

use App\Models\Note;
use App\Models\Folder;
use App\Models\Trash;
use App\Models\Safe;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class ProfileView extends Component
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

    // Состояние модального окна сброса пароля сейфа
    public bool $confirmingResetSafePassword = false;

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

        // Проверка возможности смены пароля (не демо и не Google аккаунт)
        $this->canChangePassword = !$user->isDemoUser() && !$user->hasGoogleAccount();
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

        $this->dispatch('notify', ['message' => 'Профиль успешно сохранён', 'type' => 'success']);
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

        $this->validate([
            'currentPassword' => 'required|string',
            'newPassword' => 'required|string|min:8',
            'confirmPassword' => 'required|string|same:newPassword',
        ]);

        $user = Auth::user();

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

    // Удалить пароль сейфа
    public function removeSafePassword(): void
    {
        $this->closeResetSafePasswordModal();

        $user = Auth::user();

        $safe = Safe::where('user_id', $user->id)->first();

        if ($safe) {
            $safe->resetPassword();
        }

        $this->hasSafePassword = false;
        $this->safeCurrentPassword = '';
        $this->safePassword = '';
        $this->safeConfirmPassword = '';

        $this->dispatch('notify', ['message' => 'Пароль сейфа удалён', 'type' => 'success']);
    }

    //  Отменить изменения.
    public function cancel(): void
    {
        $this->mount();
        $this->dispatch('notify', ['message' => 'Изменения отменены', 'type' => 'info']);
    }

    // Открыть модальное окно подтверждения сброса пароля сейфа
    public function confirmResetSafePassword(): void
    {
        $this->confirmingResetSafePassword = true;
    }

    // Закрыть модальное окно подтверждения сброса пароля сейфа
    public function closeResetSafePasswordModal(): void
    {
        $this->confirmingResetSafePassword = false;
        $this->dispatch('modalClosed');
    }

    public function render()
    {
        return view('livewire.profile');
    }
}