<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Политика конфиденциальности — LeafNote</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gradient-to-br from-indigo-50 to-purple-50 min-h-screen">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b border-gray-100">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <!-- Title -->
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-100 rounded-full mb-4">
                        <x-logo />
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">
                        Политика конфиденциальности
                    </h1>
                    <p class="text-gray-500 text-sm">
                        Последнее обновление: {{ now()->format('d.m.Y') }}
                    </p>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 py-8 sm:py-12">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6 sm:p-8">
                    <!-- Intro -->
                    <div class="prose prose-indigo max-w-none text-gray-700 mb-8">
                        <p class="text-sm leading-relaxed">
                            Настоящая Политика конфиденциальности описывает, какие данные мы собираем, как используем их
                            и какие у вас есть права в отношении этой информации. Используя наш сайт LeafNote, вы
                            соглашаетесь с условиями данной Политики.
                        </p>
                    </div>

                    <!-- Content -->
                    <div class="prose prose-indigo max-w-none space-y-6 text-gray-700">
                        <section>
                            <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                <i data-lucide="database" class="w-5 h-5 text-indigo-500"></i>
                                1. Какие данные мы собираем
                            </h2>
                            <p class="text-sm leading-relaxed mb-3">
                                Мы можем собирать следующие категории данных:
                            </p>

                            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                <h3 class="text-sm font-medium text-gray-900 mb-2">1.1. Данные, которые вы
                                    предоставляете самостоятельно</h3>
                                <ul class="text-sm space-y-1 ml-4 list-disc list-inside">
                                    <li>Имя</li>
                                    <li>Email</li>
                                    <li>Номер телефона</li>
                                    <li>Информация, отправленная через формы обратной связи</li>
                                </ul>
                            </div>

                            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                <h3 class="text-sm font-medium text-gray-900 mb-2">1.2. Автоматически собираемые данные
                                </h3>
                                <ul class="text-sm space-y-1 ml-4 list-disc list-inside">
                                    <li>IP‑адрес</li>
                                    <li>Тип устройства и браузера</li>
                                    <li>Файлы cookie</li>
                                    <li>Страницы, которые вы посещаете</li>
                                    <li>Время и дата посещения</li>
                                </ul>
                            </div>

                            <div class="bg-gray-50 rounded-lg p-4">
                                <h3 class="text-sm font-medium text-gray-900 mb-2">1.3. Данные от сторонних сервисов
                                </h3>
                                <p class="text-sm leading-relaxed">
                                    Если вы используете авторизацию через соцсети или внешние сервисы, мы можем получать
                                    часть данных от них (например, имя и email).
                                </p>
                            </div>
                        </section>

                        <section>
                            <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                <i data-lucide="settings" class="w-5 h-5 text-indigo-500"></i>
                                2. Как мы используем данные
                            </h2>
                            <p class="text-sm leading-relaxed mb-3">
                                Мы используем ваши данные для:
                            </p>
                            <ul class="text-sm space-y-2 ml-4 list-disc list-inside">
                                <li>предоставления и улучшения работы сайта</li>
                                <li>обратной связи и обработки запросов</li>
                                <li>отправки уведомлений (если вы дали согласие)</li>
                                <li>аналитики и статистики</li>
                                <li>защиты от мошенничества и нарушений</li>
                            </ul>
                        </section>

                        <section>
                            <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                <i data-lucide="share-2" class="w-5 h-5 text-indigo-500"></i>
                                3. Передача данных третьим лицам
                            </h2>
                            <p class="text-sm leading-relaxed mb-3">
                                Мы не продаём ваши персональные данные. Передача возможна только в следующих случаях:
                            </p>
                            <ul class="text-sm space-y-2 ml-4 list-disc list-inside">
                                <li>сервисы аналитики (например, Google Analytics)</li>
                                <li>сервисы email‑рассылок</li>
                                <li>хостинг‑провайдеры</li>
                                <li>выполнение требований закона (по запросу государственных органов)</li>
                            </ul>
                            <p class="text-sm leading-relaxed mt-3 text-gray-600">
                                Все партнёры обязаны соблюдать конфиденциальность данных.
                            </p>
                        </section>

                        <section>
                            <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                <i data-lucide="cookie" class="w-5 h-5 text-indigo-500"></i>
                                4. Cookies и технологии отслеживания
                            </h2>
                            <p class="text-sm leading-relaxed mb-3">
                                Мы используем cookies для:
                            </p>
                            <ul class="text-sm space-y-2 ml-4 list-disc list-inside">
                                <li>сохранения настроек пользователя</li>
                                <li>анализа трафика</li>
                                <li>улучшения качества работы сайта</li>
                            </ul>
                            <p class="text-sm leading-relaxed mt-3 text-gray-600">
                                Вы можете отключить cookies в настройках браузера, но часть функций сайта может работать
                                некорректно.
                            </p>
                        </section>

                        <section>
                            <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                <i data-lucide="hard-drive" class="w-5 h-5 text-indigo-500"></i>
                                5. Хранение данных
                            </h2>
                            <p class="text-sm leading-relaxed">
                                Мы храним ваши данные только столько, сколько необходимо для целей, описанных в этой
                                Политике, или в соответствии с требованиями закона.
                            </p>
                        </section>

                        <section>
                            <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                <i data-lucide="lock" class="w-5 h-5 text-indigo-500"></i>
                                6. Защита данных
                            </h2>
                            <p class="text-sm leading-relaxed mb-3">
                                Мы применяем технические и организационные меры безопасности для защиты данных от:
                            </p>
                            <ul class="text-sm space-y-2 ml-4 list-disc list-inside">
                                <li>несанкционированного доступа</li>
                                <li>изменения</li>
                                <li>утраты</li>
                                <li>раскрытия</li>
                            </ul>
                            <p class="text-sm leading-relaxed mt-3 text-gray-600">
                                Однако ни один метод передачи данных через интернет не является абсолютно безопасным.
                            </p>
                        </section>

                        <section>
                            <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                <i data-lucide="user" class="w-5 h-5 text-indigo-500"></i>
                                7. Ваши права
                            </h2>
                            <p class="text-sm leading-relaxed mb-3">
                                Вы имеете право:
                            </p>
                            <ul class="text-sm space-y-2 ml-4 list-disc list-inside">
                                <li>запросить копию ваших данных</li>
                                <li>исправить или обновить информацию</li>
                                <li>удалить данные («право быть забытым»)</li>
                                <li>отозвать согласие на обработку</li>
                                <li>ограничить обработку данных</li>
                            </ul>
                            <p class="text-sm leading-relaxed mt-3 text-gray-600">
                                Для реализации прав напишите нам на email: <a href="mailto:support@leafnote.com"
                                    class="text-indigo-600 hover:text-indigo-700">support@leafnote.com</a>
                            </p>
                        </section>

                        <section>
                            <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                <i data-lucide="refresh-cw" class="w-5 h-5 text-indigo-500"></i>
                                8. Изменения в политике
                            </h2>
                            <p class="text-sm leading-relaxed">
                                Мы можем периодически обновлять Политику. Новая версия вступает в силу с момента
                                публикации на сайте.
                            </p>
                        </section>

                        <section>
                            <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                <i data-lucide="mail" class="w-5 h-5 text-indigo-500"></i>
                                9. Контакты
                            </h2>
                            <p class="text-sm leading-relaxed mb-3">
                                Если у вас есть вопросы по Политике конфиденциальности, свяжитесь с нами:
                            </p>
                            <div class="bg-indigo-50 rounded-lg p-4">
                                <p class="text-sm">
                                    <strong>Email:</strong> <a href="mailto:support@leafnote.com"
                                        class="text-indigo-600 hover:text-indigo-700">support@leafnote.com</a>
                                </p>
                            </div>
                        </section>
                    </div>

                    <!-- Footer Info -->
                    <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                        <p class="text-xs text-gray-500">
                            &copy; {{ date('Y') }} LeafNote. Все права защищены.
                        </p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-100 py-4">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <a href="{{ url()->previous() ?: route('app') }}"
                    class="text-sm text-gray-600 hover:text-indigo-600 transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4 inline mr-1"></i>
                    Вернуться
                </a>
            </div>
        </footer>
    </div>

    <script>
        // Инициализация иконок Lucide
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>
</body>

</html>
