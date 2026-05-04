<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Условия использования — LeafNote</title>
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
                        Условия использования
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
                            Добро пожаловать на LeafNote. Используя наш сайт, вы соглашаетесь с настоящими Условиями
                            использования. Пожалуйста, внимательно прочитайте их перед началом работы.
                        </p>
                    </div>

                    <!-- Content -->
                    <div class="prose prose-indigo max-w-none space-y-6 text-gray-700">
                        <section>
                            <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                <i data-lucide="info" class="w-5 h-5 text-indigo-500"></i>
                                1. Общие положения
                            </h2>
                            <ul class="text-sm space-y-2 ml-4 list-decimal list-inside">
                                <li>Настоящие Условия регулируют порядок использования сайта LeafNote и всех
                                    предоставляемых на
                                    нём сервисов.
                                </li>
                                <li>Используя сайт, вы подтверждаете, что ознакомились с Условиями и принимаете их.</li>
                                <li>Если вы не согласны с Условиями, пожалуйста, прекратите использование сайта.</li>
                            </ul>
                        </section>

                        <section>
                            <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                <i data-lucide="mouse-pointer-2" class="w-5 h-5 text-indigo-500"></i>
                                2. Использование сайта
                            </h2>
                            <p class="text-sm leading-relaxed mb-3">
                                Вы обязуетесь использовать сайт только в законных целях.
                            </p>
                            <p class="text-sm leading-relaxed mb-3 font-medium text-gray-900">Запрещается:</p>
                            <ul class="text-sm space-y-2 ml-4 list-disc list-inside">
                                <li>нарушать работу сайта или пытаться получить несанкционированный доступ</li>
                                <li>размещать вредоносный код</li>
                                <li>использовать сайт для рассылки спама</li>
                                <li>копировать или распространять материалы без разрешения</li>
                            </ul>
                            <p class="text-sm leading-relaxed mt-3 text-gray-600">
                                Мы можем ограничить доступ к сайту пользователям, нарушающим Условия.
                            </p>
                        </section>

                        <section>
                            <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                <i data-lucide="user-circle" class="w-5 h-5 text-indigo-500"></i>
                                3. Регистрация и аккаунт
                            </h2>
                            <ul class="text-sm space-y-2 ml-4 list-decimal list-inside">
                                <li>Для доступа к отдельным функциям может потребоваться регистрация.</li>
                                <li>Вы обязуетесь предоставлять точную и актуальную информацию.</li>
                                <li>Вы несёте ответственность за сохранность данных своего аккаунта.</li>
                                <li>Мы можем удалить или заблокировать аккаунт при нарушении Условий.</li>
                            </ul>
                        </section>

                        <section>
                            <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                <i data-lucide="file-text" class="w-5 h-5 text-indigo-500"></i>
                                4. Контент
                            </h2>
                            <ul class="text-sm space-y-2 ml-4 list-decimal list-inside">
                                <li>Все материалы сайта (тексты, изображения, логотипы, дизайн) принадлежат LeafNote или
                                    используются на основании лицензии.
                                </li>
                                <li>Запрещено копировать, изменять или распространять материалы без письменного
                                    разрешения.</li>
                                <li>Пользовательский контент остаётся вашей собственностью, но вы предоставляете нам
                                    право
                                    использовать его для работы сайта.
                                </li>
                            </ul>
                        </section>

                        <section>
                            <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                <i data-lucide="alert-triangle" class="w-5 h-5 text-indigo-500"></i>
                                5. Ограничение ответственности
                            </h2>
                            <ul class="text-sm space-y-2 ml-4 list-decimal list-inside">
                                <li>Сайт предоставляется «как есть» без гарантий точности, доступности или бесперебойной
                                    работы.
                                </li>
                                <li>Мы не несём ответственности за:
                                    <ul class="text-sm space-y-1 ml-4 list-disc list-inside mt-1">
                                        <li>сбои в работе сайта</li>
                                        <li>потерю данных</li>
                                        <li>действия третьих лиц</li>
                                        <li>последствия использования информации с сайта</li>
                                    </ul>
                                </li>
                                <li>Вы используете сайт на свой риск.</li>
                            </ul>
                        </section>

                        <section>
                            <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                <i data-lucide="external-link" class="w-5 h-5 text-indigo-500"></i>
                                6. Ссылки на сторонние ресурсы
                            </h2>
                            <ul class="text-sm space-y-2 ml-4 list-decimal list-inside">
                                <li>На сайте могут быть ссылки на сторонние сайты.</li>
                                <li>Мы не контролируем их содержание и не несём ответственность за их работу.</li>
                            </ul>
                        </section>

                        <section>
                            <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                <i data-lucide="refresh-cw" class="w-5 h-5 text-indigo-500"></i>
                                7. Изменения условий
                            </h2>
                            <ul class="text-sm space-y-2 ml-4 list-decimal list-inside">
                                <li>Мы можем обновлять Условия использования.</li>
                                <li>Новая версия вступает в силу с момента публикации на сайте.</li>
                                <li>Продолжая использовать сайт, вы принимаете обновлённые Условия.</li>
                            </ul>
                        </section>

                        <section>
                            <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                <i data-lucide="log-out" class="w-5 h-5 text-indigo-500"></i>
                                8. Прекращение использования
                            </h2>
                            <ul class="text-sm space-y-2 ml-4 list-decimal list-inside">
                                <li>Вы можете прекратить использование сайта в любой момент.</li>
                                <li>Мы можем ограничить доступ или удалить аккаунт при нарушении Условий.</li>
                            </ul>
                        </section>

                        <section>
                            <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                <i data-lucide="mail" class="w-5 h-5 text-indigo-500"></i>
                                9. Контакты
                            </h2>
                            <p class="text-sm leading-relaxed mb-3">
                                Если у вас есть вопросы по Условиям использования, свяжитесь с нами:
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
           </script>
</body>

</html>
