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

<body class="font-sans text-gray-900 antialiased">
    @if (session('clear_session_storage'))
        <script>
            sessionStorage.clear();
        </script>
    @endif

    <!-- Global Loader -->
    <div x-data="{ loading: false }" x-on:loading-start="loading = true" x-on:loading-stop="loading = false"
        x-show="loading" x-cloak class="fixed inset-0 bg-white flex flex-col items-center justify-center z-50">
        <x-loader class="w-20 h-20 animate-spin text-indigo-600" />
        <p class="mt-4 text-gray-600 text-lg font-medium text-center">Пожалуйста, подождите...</p>
    </div>

    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            {{ $slot }}
        </div>
    </div>

    <livewire:connection-status />
</body>

</html>
