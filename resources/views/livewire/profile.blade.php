<div>
    <!-- Header Section -->
    <x-header :heading='$heading' :subheading='$subheading' />
    <!-- Content Section -->
    <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 mb-6 py-6">
        <div class="bg-white rounded-xl shadow-md p-6">
            <form wire:submit.prevent="saveProfile" class="space-y-8">
                <!-- Секция: Личные данные -->
                <div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i data-lucide="user" class="w-5 h-5 text-indigo-600"></i>
                        Личные данные
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Имя -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Имя
                            </label>
                            <input type="text" id="name" wire:model="name" autofocus
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow"
                                placeholder="Введите имя">
                            @error('name')
                                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Электронная почта
                            </label>
                            <input type="email" id="email" wire:model="email"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow"
                                placeholder="email@example.com">
                            @error('email')
                                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                <!-- Разделитель -->
                <div class="border-t border-gray-200"></div>
                <!-- Секция: Статистика -->
                <div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i data-lucide="bar-chart-2" class="w-5 h-5 text-indigo-600"></i>
                        Статистика
                    </h3>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                    <i data-lucide="file-text" class="w-5 h-5 text-indigo-600"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Заметок</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ $notesCount }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                                    <i data-lucide="list-checks" class="w-5 h-5 text-purple-600"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Списков</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ $checklistsCount }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                                    <i data-lucide="folder" class="w-5 h-5 text-green-600"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Папок</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ $foldersCount }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Разделитель -->
                <div class="border-t border-gray-200"></div>
                <!-- Секция: Настройки -->
                <div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i data-lucide="settings" class="w-5 h-5 text-indigo-600"></i>
                        Настройки
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Уведомления -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Уведомления
                            </label>
                            <p class="text-sm text-gray-500 mb-3">Получать уведомления о важных событиях</p>
                            <x-dropdown :options="[
                                ['value' => '1', 'text' => 'Включено'],
                                ['value' => '0', 'text' => 'Отключено'],
                            ]" selected="{{ $notificationsEnabled ? '1' : '0' }}"
                                wireModel="notificationsEnabled" width="180px" />
                        </div>
                        <!-- Автоматическое удаление корзины -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Автоматическое удаление корзины
                            </label>
                            <p class="text-sm text-gray-500 mb-3">Корзина будет автоматически очищаться выбранный период
                            </p>
                            <x-dropdown :options="[
                                ['value' => 'disabled', 'text' => 'Отключено'],
                                ['value' => '1min', 'text' => '1 мин (тест)'],
                                ['value' => '7', 'text' => '7 дней'],
                                ['value' => '14', 'text' => '14 дней'],
                                ['value' => '30', 'text' => '30 дней'],
                            ]" selected="{{ $autoDeleteDays }}" wireModel="autoDeleteDays"
                                width="180px" />
                        </div>
                    </div>
                </div>
                <!-- Разделитель -->
                <div class="border-t border-gray-200"></div>
                <!-- Flex контейнер для паролей -->
                <div class="flex flex-col lg:flex-row gap-8">
                    <!-- Секция: Смена пароля -->
                    <div class="flex-1">
                        <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <i data-lucide="key" class="w-5 h-5 text-indigo-600"></i>
                            Сменить пароль аккаунта
                            @unless ($canChangePassword)
                                <span class="text-red-600">нельзя</span>
                            @endunless
                        </h3>
                        @if ($canChangePassword)
                            <!-- Текущий пароль -->
                            <div class="mb-4">
                                <label for="currentPassword" class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Текущий пароль
                                </label>
                                <input type="password" id="currentPassword" wire:model="currentPassword"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow h-11"
                                    placeholder="Введите текущий пароль">
                                @error('currentPassword')
                                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <!-- Новый пароль -->
                            <div class="mb-4">
                                <label for="newPassword" class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Новый пароль
                                </label>
                                <input type="password" id="newPassword" wire:model="newPassword"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow h-11"
                                    placeholder="Минимум 8 символов">
                                @error('newPassword')
                                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <!-- Подтверждение пароля -->
                            <div class="mb-4">
                                <label for="confirmPassword" class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Подтверждение пароля
                                </label>
                                <input type="password" id="confirmPassword" wire:model="confirmPassword"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow h-11"
                                    placeholder="Повторите новый пароль">
                                @error('confirmPassword')
                                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @else
                            <!-- Текущий пароль (заблокирован) -->
                            <div class="mb-4">
                                <label for="currentPassword" class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Текущий пароль
                                </label>
                                <input type="password" id="currentPassword" disabled readonly
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-600 cursor-not-allowed h-11 transition-shadow"
                                    placeholder="Недоступно">
                            </div>
                            <!-- Новый пароль (заблокирован) -->
                            <div class="mb-4">
                                <label for="newPassword" class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Новый пароль
                                </label>
                                <input type="password" id="newPassword" disabled readonly
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-600 cursor-not-allowed h-11 transition-shadow"
                                    placeholder="Недоступно">
                            </div>
                            <!-- Подтверждение пароля (заблокирован) -->
                            <div class="mb-4">
                                <label for="confirmPassword" class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Подтверждение пароля
                                </label>
                                <input type="password" id="confirmPassword" disabled readonly
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-600 cursor-not-allowed h-11 transition-shadow"
                                    placeholder="Недоступно">
                            </div>
                        @endif
                    </div>
                    <!-- Секция: Пароль сейфа -->
                    <div class="flex-1">
                        <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <i data-lucide="lock" class="w-5 h-5 text-indigo-600"></i>
                            Пароль сейфа
                            @if ($hasSafePassword)
                                <span class="text-indigo-700">установлен</span>
                            @else
                                <span class="text-red-600">не установлен</span>
                            @endif
                        </h3>
                        @if ($hasSafePassword)
                            <!-- Текущий пароль сейфа -->
                            <div class="mb-4">
                                <label for="safeCurrentPassword"
                                    class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Текущий пароль сейфа
                                </label>
                                <input type="password" id="safeCurrentPassword" wire:model="safeCurrentPassword"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow h-11"
                                    placeholder="Введите текущий пароль сейфа">
                                @error('safeCurrentPassword')
                                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @else
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Информация
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                                        <i data-lucide="info" class="w-5 h-5 text-gray-500"></i>
                                    </div>
                                    <input type="text" disabled readonly
                                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-600 cursor-not-allowed pr-10 h-11 transition-shadow"
                                        value="Установите пароль для защиты ваших заметок в сейфе">
                                </div>
                            </div>
                        @endif
                        <!-- Новый пароль сейфа -->
                        <div class="mb-4">
                            <label for="safePassword" class="block text-sm font-medium text-gray-700 mb-1.5">
                                {{ $hasSafePassword ? 'Новый пароль сейфа' : 'Создать пароль сейфа' }}
                            </label>
                            <input type="password" id="safePassword" wire:model="safePassword"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow h-11"
                                placeholder="Минимум 4 символа">
                            @error('safePassword')
                                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- Подтверждение пароля сейфа -->
                        <div class="mb-4">
                            <label for="safeConfirmPassword" class="block text-sm font-medium text-gray-700 mb-1.5">
                                {{ $hasSafePassword ? 'Подтверждение нового пароля' : 'Подтверждение пароля сейфа' }}
                            </label>
                            <input type="password" id="safeConfirmPassword" wire:model="safeConfirmPassword"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow h-11"
                                placeholder="Повторите пароль">
                            @error('safeConfirmPassword')
                                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                <!-- Разделитель -->
                <div class="border-t border-gray-200"></div>
                <!-- Кнопки действий -->
                <div class="flex flex-wrap items-center gap-3 justify-end">
                    @if ($hasSafePassword)
                        <x-button-reset-password-safe wire:click.prevent="confirmResetSafePassword"
                            wire:loading.attr="disabled" />
                    @endif
                    <x-button-save wire:click.prevent="saveProfile" wire:loading.attr="disabled" />
                </div>
            </form>
        </div>
    </div>
    <!-- Модальное окно подтверждения сброса пароля сейфа -->
    <x-modal type="confirm" :show="$confirmingResetSafePassword" title="Сбросить пароль сейфа?"
        description="Вход в сейф будет осуществляться без необходимости ввода пароля." icon="lock"
        confirmText="Сбросить" confirmMethod="removeSafePassword" cancelMethod="closeResetSafePasswordModal" />
</div>
