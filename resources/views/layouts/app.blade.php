<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>


<body class="bg-gradient-to-br from-indigo-50 to-purple-50 min-h-screen">
    {{-- Глобальное состояние приложения/навигации --}}
    <livewire:app-state />
    {{-- Новый Livewire layout-контейнер --}}
    <livewire:layouts.app-layout />


    <!-- Боковое меню NavigationSidebar -->

    {{-- <livewire:navigation-sidebar :section="$section" :folder-id="$folderId" /> --}}

    <div class="ml-16">

        <!-- Header HeaderPage -->
        {{-- <livewire:is :component="'headers.header-' . $section" :section="$section" :folder-id="$folderId" /> --}}
        {{-- <livewire:is component="headers.header-router" /> --}}


        <!-- ControlPanel -->

        {{-- <livewire:is :component="'control-panels.control-panel-' . session('section', 'dashboard')" :section="session('section', 'dashboard')" :folder-id="session('folderId')" /> --}}

        {{-- <livewire:is :component="'control-panels.control-panel-' . $section" :section="$section" :folder-id="$folderId" /> --}}

        <!-- Content ContentPage -->

        {{-- <livewire:is :component="'content.content-' . session('section', 'dashboard')" :section="session('section', 'dashboard')" :folder-id="session('folderId')" :search="session('search', '')" /> --}}

        {{-- <livewire:is :component="'content.content-' . $section" :section="$section" :folder-id="$folderId" :search="$search" /> --}}

        <!-- Пагинация Pagination-->

        {{-- <livewire:pagination /> --}}

        <!-- Footer FooterPage-->

        {{-- <livewire:footer /> --}}
    </div>


    @livewireScripts
    {{-- <script src="https://unpkg.com/lucide@latest"></script> --}}

    {{-- <script>
        function initLucide() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
        document.addEventListener('DOMContentLoaded', initLucide);
    </script> --}}


</body>

</html>
