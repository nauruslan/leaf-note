<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;


Route::get('/test-email', function () {
    Mail::raw('Это тестовое письмо', function ($message) {
        $message->to('test@example.com')
                ->subject('Тестовое письмо');
    });

    return 'Письмо отправлено! Проверьте Mailpit на порту 8025';
});

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';