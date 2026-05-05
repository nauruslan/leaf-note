<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>404 - Страница не найдена</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css'])
</head>

<body class="bg-gradient-to-br from-indigo-50 to-purple-50 min-h-screen">
    <div class="flex flex-col items-center justify-center min-h-screen px-4 gap-5">
        <!-- 404 Number -->
        <div class="relative">
            <h1
                class="text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600 leading-none">
                404
            </h1>
        </div>

        <!-- Message -->
        <div class="text-center mt-[-20px] md:mt-[-30px]">
            <h2 class="text-2xl md:text-3xl font-semibold text-gray-700 mb-2">
                Страница не найдена
            </h2>
            <p class="text-gray-500 text-sm md:text-base max-w-md">
                К сожалению, страница, которую вы ищете, не существует или была перемещена.
            </p>
        </div>

        <!-- Button to Home -->
        <a href="{{ route('app') }}" class="mt-6">
            <x-primary-button>
                На главную
            </x-primary-button>
        </a>
    </div>
</body>

</html>
