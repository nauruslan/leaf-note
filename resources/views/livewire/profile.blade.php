<div>
    <!-- Header Section -->
    <x-header :heading='$heading' :subheading='$subheading' />
    <!-- Content Section -->
    <x-profile-content-section :name="$name" :email="$email" :notesCount="$this->statistics->notesCount" :checklistsCount="$this->statistics->checklistsCount" :foldersCount="$this->statistics->foldersCount"
        :notificationsEnabled="$notificationsEnabled" :autoDeleteDays="$autoDeleteDays" :canChangePassword="$canChangePassword" :hasSafePassword="$hasSafePassword" />

    <!-- Модальное окно подтверждения сброса пароля аккаунта -->
    <x-modal :show="$this->isModalOpen('confirm') && $this->getModalData('confirm', 'modalType') === 'accountPasswordReset'" type="confirm" :title="$this->getModalTitle('confirm')" :description="$this->getModalDescription('confirm')" :confirmText="$this->getModalData('confirm', 'confirmText')"
        confirmMethod="sendAccountPasswordResetLink" cancelMethod="closeModal('confirm')" />

    <!-- Модальное окно подтверждения сброса пароля сейфа -->
    <x-modal :show="$this->isModalOpen('confirm') && $this->getModalData('confirm', 'modalType') === 'safePasswordReset'" type="confirm" :title="$this->getModalTitle('confirm')" :description="$this->getModalDescription('confirm')" :confirmText="$this->getModalData('confirm', 'confirmText')"
        confirmMethod="sendSafePasswordResetLink" cancelMethod="closeModal('confirm')" />
</div>
