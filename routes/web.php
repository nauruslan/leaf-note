<?php

use App\Http\Controllers\NoteImageController;
use Illuminate\Support\Facades\Route;

// Очистка сессии на время разработки
Route::get('/clear-session', function() {
    session()->flush();
    return redirect()->route('login');
});

// Главная страница (требует аутентификации и верификации)
Route::view('/', 'layouts.app')->middleware(['auth', 'verified'])->name('app');

// Загрузка изображения
Route::post('/notes/upload-image', [NoteImageController::class, 'upload'])
    ->name('notes.upload-image')
    ->middleware('throttle:6,1'); // Дополнительно: защита от спама (опционально)

// Удаление изображения
Route::delete('/notes/delete-image', [NoteImageController::class, 'delete'])
    ->name('notes.delete-image')
    ->middleware('throttle:6,1'); // Дополнительно: защита от спама (опционально)

// Подключение маршрутов из auth.php
require __DIR__.'/auth.php';