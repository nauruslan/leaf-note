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
    ->name('notes.upload-image');

// Удаление изображения
Route::delete('/notes/delete-image', [NoteImageController::class, 'delete'])
    ->name('notes.delete-image');

// Мягкое удаление изображения (для undo/redo)
Route::post('/notes/soft-delete-image', [NoteImageController::class, 'softDelete'])
    ->name('notes.soft-delete-image');

// Восстановление изображения (при undo)
Route::post('/notes/restore-image', [NoteImageController::class, 'restore'])
    ->name('notes.restore-image');

// Выполнение отложенного удаления
Route::post('/notes/execute-deletion', [NoteImageController::class, 'executeDeletion'])
    ->name('notes.execute-deletion');

// Подключение маршрутов из auth.php
require __DIR__.'/auth.php';