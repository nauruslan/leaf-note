<div>
    <!-- Header Section -->
    <x-header :heading='$heading' :subheading='$subheading' />
    <!-- Content Section -->
    <x-profile-content-section :name="$name" :email="$email" :notesCount="$this->statistics->notesCount" :checklistsCount="$this->statistics->checklistsCount" :foldersCount="$this->statistics->foldersCount"
        :notificationsEnabled="$notificationsEnabled" :autoDeleteDays="$autoDeleteDays" :canChangePassword="$canChangePassword" :hasSafePassword="$hasSafePassword" />

    <!-- Модальное окно подтверждения сброса пароля аккаунта -->
    <x-modal :show="$showPasswordResetModal" type="confirm" title="Сброс пароля аккаунта"
        description="Вы уверены, что хотите сбросить пароль аккаунта? Ссылка для сброса будет отправлена на вашу почту."
        confirmMethod="sendAccountPasswordResetLink" cancelMethod="closeAccountPasswordResetModal"
        confirmText="Да, отправить" />

    <!-- Модальное окно подтверждения сброса пароля сейфа -->
    <x-modal :show="$showSafePasswordResetModal" type="confirm" title="Сброс пароля сейфа"
        description="Вы уверены, что хотите сбросить пароль сейфа? Ссылка для сброса будет отправлена на вашу почту."
        confirmMethod="sendSafePasswordResetLink" cancelMethod="closeSafePasswordResetModal"
        confirmText="Да, отправить" />
</div>
