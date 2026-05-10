@props([
    'name' => '',
    'email' => '',
    'notesCount' => 0,
    'checklistsCount' => 0,
    'foldersCount' => 0,
    'notificationsEnabled' => false,
    'autoDeleteDays' => 'disabled',
    'canChangePassword' => true,
    'hasSafePassword' => false,
])

<div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 mb-6 py-6">
    <div class="bg-white rounded-xl shadow-md p-6">
        <form wire:submit.prevent="saveProfile" class="space-y-7">
            <!-- Секция: Личные данные -->
            <div>
                <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <i data-lucide="user" class="w-5 h-5 w-5 h-5 text-indigo-600"></i>
                    Личные данные
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Имя -->
                    <x-input-group label="Имя" for="name" type="text" id="name" wireModel="name" autofocus
                        placeholder="Введите имя" field="name" />
                    <!-- Email -->
                    <x-input-group label="Электронная почта" for="email" type="email" id="email"
                        wireModel="email" placeholder="Введите почту" field="email" />
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
                <div class="flex flex-wrap justify-evenly gap-8">
                    <x-card-profile icon="file-text" title="Заметок" :content="$notesCount" />
                    <x-card-profile icon="list-checks" title="Списков" :content="$checklistsCount" />
                    <x-card-profile icon="folder" title="Папок" :content="$foldersCount" />
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
                        <x-dropdown :options="[['value' => '1', 'text' => 'Включено'], ['value' => '0', 'text' => 'Отключено']]" selected="{{ $notificationsEnabled ? '1' : '0' }}"
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
                            <x-input-group label="Текущий пароль" for="currentPassword" type="password"
                                id="currentPassword" wireModel="currentPassword" placeholder="Введите текущий пароль"
                                field="currentPassword" />
                        </div>
                        <!-- Новый пароль -->
                        <div class="mb-4">
                            <x-input-group label="Новый пароль" for="newPassword" type="password" id="newPassword"
                                wireModel="newPassword" placeholder="Минимум 8 символов" field="newPassword" />
                        </div>
                        <!-- Подтверждение пароля -->
                        <div class="mb-4">
                            <x-input-group label="Подтверждение пароля" for="confirmPassword" type="password"
                                id="confirmPassword" wireModel="confirmPassword" placeholder="Повторите новый пароль"
                                field="confirmPassword" />
                        </div>
                    @else
                        <!-- Текущий пароль (заблокирован) -->
                        <div class="mb-4">
                            <x-input-group label="Текущий пароль" for="currentPassword" type="password"
                                id="currentPassword" disabled readonly placeholder="Недоступно" />
                        </div>
                        <!-- Новый пароль (заблокирован) -->
                        <div class="mb-4">
                            <x-input-group label="Новый пароль" for="newPassword" type="password" id="newPassword"
                                disabled readonly placeholder="Недоступно" />
                        </div>
                        <!-- Подтверждение пароля (заблокирован) -->
                        <div class="mb-4">
                            <x-input-group label="Подтверждение пароля" for="confirmPassword" type="password"
                                id="confirmPassword" disabled readonly placeholder="Недоступно" />
                        </div>
                    @endif
                    <!-- Кнопка-ссылка "Забыли пароль аккаунта?" -->
                    <div class="mt-2">
                        <x-button-forgot text="Забыли пароль аккаунта?" wireClick="openAccountPasswordResetModal()"
                            :disabled="!$canChangePassword" />
                    </div>
                </div>
                <!-- Секция: Пароль сейфа -->
                <div class="flex-1">
                    <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i data-lucide="lock" class="w-5 h-5 text-indigo-600"></i>
                        Пароль сейфа
                        @if ($hasSafePassword)
                            <button type="button" wire:click="openSafePasswordResetModal"
                                class="bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent font-medium hover:opacity-80 transition-opacity cursor-pointer">
                                cбросить
                            </button>
                        @else
                            <span class="text-red-600">не установлен</span>
                        @endif
                    </h3>
                    @if ($hasSafePassword)
                        <!-- Текущий пароль сейфа -->
                        <div class="mb-4">
                            <x-input-group label="Текущий пароль сейфа" for="safeCurrentPassword" type="password"
                                id="safeCurrentPassword" wireModel="safeCurrentPassword"
                                placeholder="Введите текущий пароль сейфа" field="safeCurrentPassword" />
                        </div>
                    @else
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Информация
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                                    <i data-lucide="info" class="w-5 h-5 text-gray-500"></i>
                                </div>
                                <input type="text" disabled readonly
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-600 cursor-not-allowed pr-10 h-10 transition-shadow"
                                    value="Установите пароль для защиты ваших заметок в сейфе">
                            </div>
                        </div>
                    @endif
                    <!-- Новый пароль сейфа -->
                    <div class="mb-4">
                        <x-input-group :label="$hasSafePassword ? 'Новый пароль сейфа' : 'Создать пароль сейфа'" for="safePassword" type="password" id="safePassword"
                            wireModel="safePassword" placeholder="Минимум 4 символа" field="safePassword" />
                    </div>
                    <!-- Подтверждение пароля сейфа -->
                    <div class="mb-4">
                        <x-input-group :label="$hasSafePassword ? 'Подтверждение нового пароля' : 'Подтверждение пароля сейфа'" for="safeConfirmPassword" type="password"
                            id="safeConfirmPassword" wireModel="safeConfirmPassword" placeholder="Повторите пароль"
                            field="safeConfirmPassword" />
                    </div>
                    <!-- Кнопка-ссылка "Забыли пароль сейфа?" -->
                    <div class="mt-2">
                        <x-button-forgot text="Забыли пароль сейфа?" wireClick="openSafePasswordResetModal()"
                            :disabled="!$hasSafePassword" />
                    </div>
                </div>
            </div>
            <!-- Разделитель -->
            <div class="border-t border-gray-200"></div>
            <!-- Кнопки действий -->
            <div class="flex flex-wrap items-center gap-3 justify-end">
                <x-button-save id="saveProfileButton" wire:click.prevent="saveProfile" target="saveProfile" />
            </div>
        </form>
    </div>
</div>
