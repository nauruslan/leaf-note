<?php

use Illuminate\Support\Facades\Route;

// Очистка сессии на время разработки
Route::get('/clear-session', function() {
    session()->flush();
    return 'Session cleared';
});

Route::view('/', 'layouts.app')->middleware(['auth', 'verified'])->name('app');


require __DIR__.'/auth.php';
